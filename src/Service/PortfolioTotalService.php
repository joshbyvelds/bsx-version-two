<?php

namespace App\Service;

use App\Entity\Debt;
use App\Entity\User;
use App\Entity\WeeklyPortfolioTotal;
use Doctrine\Persistence\ManagerRegistry;

class PortfolioTotalService
{
    private const USD_TO_CAD = 1.36;

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    /**
     * PHP port of the total calculation in templates/dashboard/panels/total_value.html.twig.
     * Keep the two in sync. Uses the user's CC assign/buyback setting.
     */
    public function calculateTotal(User $user): float
    {
        $assign = $user->getSettings()->isTotalValueCcTypeAssign();

        $usd = 0.0;
        $can = 0.0;

        foreach ($user->getStocks() as $stock) {
            $company = $stock->getCompany();
            $price = $company->getCurrentPrice();
            $isCan = $company->getCountry() === 'CAN';
            $amount = 0;
            $ccBuyback = 0.0;
            $ccAssign = 0.0;

            foreach ($stock->getShareBuys() as $buy) {
                if ($buy->getSold() < $buy->getAmount()) {
                    $amount += $buy->getAmount() - $buy->getSold();
                }
            }

            foreach ($stock->getCoveredCalls() as $cc) {
                if (!$cc->isExpired() && !$cc->isExercised() && $cc->getStrike() <= $price) {
                    $amount -= 100 * $cc->getContracts();
                    $ccBuyback += ($price * $cc->getContracts() * 100) - ($cc->getAsk() * $cc->getContracts() * 100) - 9.95 - (1.25 * $cc->getContracts());
                    $ccAssign += ($cc->getStrike() * $cc->getContracts() * 100) - 43.00;
                }
            }

            $stockTotal = 0.0;
            $buyback = 0.0;
            $assigned = 0.0;

            // Mirrors the template: stock value (buys is always 0 there, so no fee) plus CC adjustments.
            if ($amount > 0 || $ccBuyback > 0) {
                $stockTotal = $amount * $price;
                $buyback += $stockTotal + $ccBuyback;
                $assigned += $stockTotal + $ccAssign;
            }

            foreach ($stock->getOptions() as $option) {
                if (!$option->isExpired() && $option->getContracts() >= 1) {
                    $sellFee = 9.95 + ($option->getContracts() * 1.25);
                    $value = ($option->getCurrent() * 100 * $option->getContracts()) - $sellFee;
                    $buyback += $value;
                    $assigned += $value;
                }
            }

            // Quirk mirrored from the template: the CAD bucket always uses the assign figure, in both modes.
            if ($isCan) {
                $can += $assigned;
            } else {
                $usd += $assign ? $assigned : $buyback;
            }
        }

        $debtCdn = 0.0;
        $debtUsd = 0.0;
        foreach ($this->doctrine->getRepository(Debt::class)->findBy(['user' => $user->getId()]) as $debt) {
            $debtCdn += $debt->getCdn();
            $debtUsd += $debt->getUsd();
        }

        return ($usd * self::USD_TO_CAD) + $can - $debtCdn - ($debtUsd * self::USD_TO_CAD);
    }

    /**
     * Sets the current week's total, or rolls over to a new week if the last one has ended.
     * Does not flush. Returns details about what happened.
     */
    public function applyWeeklyTotal(User $user, float $total): array
    {
        $em = $this->doctrine->getManager();
        $totals = $user->getWeeklyPortfolioTotals()->toArray();
        $result = ['new' => false];

        if (!$totals) {
            return $result + ['skipped' => 'no weekly total history'];
        }

        $lastEntry = $totals[count($totals) - 1];
        $lastEndDate = (clone $lastEntry->getEndDate())->modify('+1 day');

        if ($lastEndDate >= new \DateTime('now')) {
            $lastEntry->setAmount(round($total, 2));
            return $result;
        }

        $monday = (clone $lastEndDate)->modify('next monday');
        $friday = (clone $lastEndDate)->modify('next friday');

        $lastEntry->setCurrent(false);

        $new = new WeeklyPortfolioTotal();
        $new->setUser($user);
        $new->setStartDate($monday);
        $new->setEndDate($friday);
        $new->setAmount(round($total, 2));
        $new->setCurrent(true);
        $new->setEndofmonth($monday->format('Y-m') !== $friday->format('Y-m'));
        $new->setEndofyear($monday->format('Y') !== $friday->format('Y'));

        if ($lastEndDate->format('Y-m') !== $monday->format('Y-m')) {
            $lastEntry->setEndofmonth(true);
        }
        if ($lastEndDate->format('Y') !== $monday->format('Y')) {
            $lastEntry->setEndofyear(true);
        }

        $em->persist($new);

        return ['new' => true];
    }
}

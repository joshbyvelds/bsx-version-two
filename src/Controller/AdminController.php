<?php

namespace App\Controller;

use App\Entity\Play;
use App\Entity\Stock;
use App\Entity\User;
use App\Service\PortfolioTotalService;
use App\Entity\WrittenOption;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/all/stocks', name: 'all_stocks')]
    public function showAllUsersStocks(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $user = $this->getUser();
        $settings = $user->getSettings();

        $stocks = $doctrine->getRepository(Stock::class)->findAll();
        $companyIds = [];
        $companies = [];

        foreach ($stocks as $s) {
            if ($s->getSharesOwned() <= 0) {
                continue;
            }

            $company = $s->getCompany();

            if ($company && !in_array($company->getId(), $companyIds, true)) {
                $companyIds[] = $company->getId();
                $companies[] = [$company->getId(), $s->getId()];
            }
        }

        return $this->render('admin/stocks.html.twig', [
            'stocks' => $stocks,
            'companies' => $companies,
            'settings' => $settings,
        ]);
    }

    #[Route('/all/coveredcalls', name: 'all_ccs')]
    public function showAllUsersCoveredCalls(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $user = $this->getUser();
        $settings = $user->getSettings();

        // 1. Get timezone safely from $_ENV
        $tzString = $_ENV['APP_TIMEZONE'] ?? 'UTC';
        $timezone = new \DateTimeZone($tzString);

        $days_after_expiry = $settings->getSuperAdminAllCCDaysAfterExpiry(); // e.g., 3

        // 1. Keep the exact current time, just subtract the days
        $cutoffDate = new \DateTime('now', $timezone);
        $cutoffDate->modify("-" . $days_after_expiry . " days");

        // 2. Query
        $covered_calls = $doctrine->getRepository(WrittenOption::class)
            ->createQueryBuilder('wo')
            ->where('wo.expiry >= :cutoff')
            ->andWhere('wo.buyout = 0') // Adds the buyout check
            ->setParameter('cutoff', $cutoffDate)
            ->getQuery()
            ->getResult();

        return $this->render('admin/coveredcalls.html.twig', [
            'coveredCalls' => $covered_calls,
            'settings' => $settings,
        ]);
    }

    #[Route('/all/plays', name: 'all_plays')]
    public function showAllUsersPlays(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $user = $this->getUser();
        $settings = $user->getSettings();

        $plays = $doctrine->getRepository(Play::class)->findBy(['finished' => 0]);

        return $this->render('admin/plays.html.twig', [
            'plays' => $plays,
            'settings' => $settings,
        ]);
    }

    #[Route('/all/cc/updateask', name: 'update_cc_ask', methods: 'POST')]
    public function updateCCAsk(Request $request, ManagerRegistry $doctrine): JsonResponse
    {

        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');
        $em = $doctrine->getManager();

        if ($request->isXMLHttpRequest()) {
            $user = $this->getUser();
            $settings = $user->getSettings();

            // 1. Get timezone safely from $_ENV
            $tzString = $_ENV['APP_TIMEZONE'] ?? 'UTC';
            $timezone = new \DateTimeZone($tzString);

            // 2. Calculate the cutoff
            $days_after_expiry = $settings->getSuperAdminAllCCDaysAfterExpiry();
            $cutoffDate = new \DateTime('now', $timezone);
            $cutoffDate->modify("-" . $days_after_expiry . " days");
            $cutoffDate->setTime(0, 0, 0);

            // 3. Query
            $covered_calls = $doctrine->getRepository(WrittenOption::class)
                ->createQueryBuilder('wo')
                ->where('wo.expiry >= :cutoff')
                ->setParameter('cutoff', $cutoffDate->format('Y-m-d'))
                ->getQuery()
                ->getResult();

            $updates = [];

            forEach($covered_calls as $option){
                if(!$option->isExpired() && !$option->isExercised()){
                    $e = date_format($option->getExpiry(), "Y-m-d");
                    $t = "c";
                    $s = number_format($option->getStrike(), 2);
                    $option_data = json_decode(file_get_contents('https://www.optionsprofitcalculator.com/ajax/getOptions?stock=' . $option->getStock()->getCompany()->getTicker() . '&reqId=1'), true);
                    $option_data = $option_data['options'];
                    $option_data = $option_data[$e];
                    $option_data = $option_data[$t];
                    $option_data = $option_data[$s];
                    $current = $option_data['a'];
                    $updates[] = $current;
                    $option->setAsk($current);
                }
            }

            $em->flush();

            return new JsonResponse(array('success' => true, 'updates' => $updates));
        }

        return new JsonResponse(array('success' => false, 'reason' => "Non XMLHttp Request"));
    }

    #[Route('/all/users/ids', name: 'all_user_ids', methods: 'POST')]
    public function userIds(ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $users = [];
        foreach ($doctrine->getRepository(User::class)->findAll() as $u) {
            $users[] = [$u->getId(), $u->getUsername()];
        }

        return new JsonResponse(['success' => true, 'users' => $users]);
    }

    #[Route('/all/weeklytotals/update', name: 'update_weekly_totals', methods: 'POST')]
    public function updateWeeklyTotal(Request $request, ManagerRegistry $doctrine, PortfolioTotalService $totals): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $user = $doctrine->getRepository(User::class)->find((int) $request->get('user_id'));
        if (!$user) {
            return new JsonResponse(['success' => false, 'reason' => 'User not found']);
        }

        $total = $totals->calculateTotal($user);
        $result = $totals->applyWeeklyTotal($user, $total);
        $doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'total' => round($total, 2)] + $result);
    }

    #[Route('/all/portfolio-history', name: 'all_portfolio_history')]
    public function showUserPortfolioHistory(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $current = $this->getUser();
        $account = $doctrine->getRepository(User::class)->find((int) $request->query->get('user', $current->getId())) ?? $current;

        return $this->render('admin/portfolio_history.html.twig', [
            'settings' => $current->getSettings(),
            'users' => $doctrine->getRepository(User::class)->findAll(),
            'account' => $account,
            'rows' => $this->buildHistoryRows($account),
        ]);
    }

    #[Route('/all/portfolio-history/{id}', name: 'all_portfolio_history_data', methods: 'POST')]
    public function userPortfolioHistoryData(int $id, ManagerRegistry $doctrine): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPERADMIN');

        $user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['success' => false, 'reason' => 'User not found']);
        }

        return new JsonResponse(['success' => true, 'rows' => $this->buildHistoryRows($user)]);
    }

    /**
     * Grid rows for a user's weekly portfolio totals: change vs last week, month, year and high point.
     */
    private function buildHistoryRows(User $user): array
    {
        $pct = fn (float $new, float $old): float => ($new != $old && $old != 0.0) ? round((($new - $old) / $old) * 100, 2) : 0.0;

        $rows = [];
        $last = $month = $year = $high = null;

        foreach ($user->getWeeklyPortfolioTotals() as $week) {
            $amount = (float) $week->getAmount();

            if ($last === null) {
                $last = $month = $year = $high = $amount;
                $highDiff = 0.0;
            } else {
                $highDiff = $pct($amount, $high);
                $high = max($high, $amount);
            }

            $rows[] = [
                'week_start' => $week->getStartDate()->format('m/d/Y'),
                'week_end' => $week->getEndDate()->format('m/d/Y'),
                'amount' => round($amount, 2),
                'week_change' => $pct($amount, $last),
                'month_change' => $pct($amount, $month),
                'year_change' => $pct($amount, $year),
                'high_diff' => $highDiff,
            ];

            $last = $amount;
            if ($week->isEndofmonth()) {
                $month = $amount;
            }
            if ($week->isEndofyear()) {
                $year = $amount;
            }
        }

        return $rows;
    }
}

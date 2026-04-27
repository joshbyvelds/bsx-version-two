<?php

namespace App\Controller;

use App\Entity\Play;
use App\Entity\Stock;
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

        return $this->render('admin/stocks.html.twig', [
            'stocks' => $stocks,
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
}

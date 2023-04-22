<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\Settings;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $wallet = new Wallet();
        $settings = new Settings();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Set Default Settings for User..
            $settings->setUser($user);
            $settings->setDashboardTransactions(6);
            $settings->setMaxPlayMoney(1000);
            $settings->setMaxPlays(6);
            $settings->setStocksUpdateSoldPrice(false);
            $settings->setStocksManualUpdateEnabled(false);
            $settings->setStocksDisableUpdateEnabled(false);
            $settings->setStocksDisableCanadianUpdateEnabled(false);
            $settings->setFuturesPlayBucketMax(1000);
            $settings->setFuturesProfitBucketMax(1000);
            $settings->setFuturesUseBrokerMargin(false);
            $settings->setFuturesBrokerMarginAmount(0);
            $settings->setFuturesUseSplitProfits(false);
            $settings->setFuturesWeeklyGoal(125);
            $settings->setFuturesDataFee(13);
            $settings->setFuturesProfitSplitLevel1Amount(0);
            $settings->setFuturesProfitSplitLevel2Amount(0);
            $settings->setFuturesProfitSplitLevel3Amount(0);
            $settings->setFuturesProfitSplitLevel4Amount(0);
            $settings->setFuturesProfitSplitLevel5Amount(0);
            $settings->setFuturesProfitSplitLevel6Amount(0);
            $settings->setFuturesProfitSplitLevel7Amount(0);
            $settings->setFuturesProfitSplitLevel1Ratio(0);
            $settings->setFuturesProfitSplitLevel2Ratio(0);
            $settings->setFuturesProfitSplitLevel3Ratio(0);
            $settings->setFuturesProfitSplitLevel4Ratio(0);
            $settings->setFuturesProfitSplitLevel5Ratio(0);
            $settings->setFuturesProfitSplitLevel6Ratio(0);
            $settings->setFuturesProfitSplitLevel7Ratio(0);
            $settings->setTheme(1);
            $settings->setCashEquityBalance(0);
            $settings->setDashboardUseHotColdMeter(false);
            $settings->setDashboardUseCashEquityBalance(false);

            // Create a Wallet
            $wallet->setUser($user);
            $wallet->setCAN(0.00);
            $wallet->setUSD(0.00);

            $entityManager->persist($user);
            $entityManager->persist($wallet);
            $entityManager->persist($settings);
            $entityManager->flush();
            
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'show_nav' => false,
            'registrationForm' => $form->createView(),
        ]);
    }
}

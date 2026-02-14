<?php

namespace App\Form;

use App\Entity\Dividend;
use App\Repository\StockRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class DividendType extends AbstractType
{
    private $stockRepository;

    public function __construct(Security $security, StockRepository $stockRepository)
    {
        $this->security = $security;
        $this->user = $this->security->getUser();
        $this->user_id = $this->user->getId();
        $this->stockRepository = $stockRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // In your FormType or Controller
        $allStocks = $this->stockRepository->findBy(['user' => $this->user_id]);

        $eligibleStocks = array_filter($allStocks, function($stock) {
            return ($stock->getSharesOwned() >= 1 && $stock->getCompany()->isPaysDividend());
        });

        $builder
            ->add('Stock', EntityType::class, [
                'label' => 'Stock',
                'class' => 'App\Entity\Stock',
                'choice_attr' => function($stock) {
                    return [
                        'data-ticker' => $stock->getCompany()->getTicker(),
                        'data-stock-name' => $stock->getCompany()->getName(),
                        'data-id' => $stock->getId(),
                    ];
                },
                'choice_label' => 'company.name',
                'choices' => $eligibleStocks,
            ])

            ->add('payment_date', DateType::class, [
                'label' => 'Payment Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                ]
            ])
            ->add('amount', TextType::class, [
                'label' => 'Amount',
                'attr' => [
                    'placeholder' => '$0.00',
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                ],
            ])
            ->add('currency',ChoiceType::class,[
                'label' => 'Currency',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                ],
                'choices' => array(
                    'CAN' => 'can',
                    'USD' => 'usd'
                ),
                'multiple'=>false,
                'expanded'=>true
            ])
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => 'normal-case cursor-pointer rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow'
                ]    
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dividend::class,
        ]);
    }
}

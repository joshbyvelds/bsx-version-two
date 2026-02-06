<?php

namespace App\Form;

use App\Entity\ShareSell;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class ShareSellType extends AbstractType
{
    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->user = $this->security->getUser();
        $this->user_id = $this->user->getId(); 
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user_id = $this->user_id;

        $builder
            ->add('Stock', EntityType::class, [
                'label' => 'Company Stock',
                'class' => 'App\Entity\Stock',
                'choice_attr' => function($stock) {
                    return [
                        'data-ticker' => $stock->getCompany()->getTicker(),
                        'data-stock-name' => $stock->getCompany()->getName(),
                        'data-id' => $stock->getId(),
                    ];
                },
                'choice_label' => function ($stock) {
                    // Accessing the nested company entity properties
                    return sprintf('(%s) %s', $stock->getCompany()->getTicker(), $stock->getCompany()->getName());
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->leftJoin('s.company', 'c')
                        ->addSelect('c')
                        ->where('s.user = :user')
                        ->setParameter('user', $this->user_id)
                        ->orderBy('c.name', 'ASC');
                },
            ])

            ->add('price', NumberType::class, [
                'label' => 'Sell Price',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00'
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Amount of Shares Sold',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => 0
                ],
            ])
            ->add('date', DateType::class, [
                'label' => 'Sell Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                ],
            ])
            ->add('payment_currency',ChoiceType::class,[
                'label' => 'Payment Currency',
                'choices' => array(
                    'CAN' => 'can',
                    'USD' => 'usd'
                ),
                'mapped' => false,
                'multiple'=>false,
                'expanded'=>true
            ])
            ->add('cost', TextType::class, [
                'mapped' => false,
                'label' => 'Total Amount',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00'
                ],
            ])
            ->add('nofee', CheckboxType::class, [
                'label' => 'No Fee',
                'required' => false,
            ])

            ->add('part_of_play', ChoiceType::class, [
                'label' => 'Part of Play?',
                'required' => false,
                'mapped' => false,
                'choices' => [
                    'Yes' => '1',
                    'No' => '0',
                ],
            ])

            ->add('play', EntityType::class, [
                'attr' => [
                    'class' => 'block w-full rounded-md bg-white py-1.5 pl-3 pr-10 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body',
                ],
                'mapped' => false,
                'label' => 'Play',
                'class' => 'App\Entity\Play',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $user_id = $this->user_id;
                    return $er->createQueryBuilder('s')
                        ->where('s.User = :user')
                        ->setParameter('user', $user_id);
                },
            ])
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary float-right'
                ]    
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShareSell::class,
        ]);
    }
}

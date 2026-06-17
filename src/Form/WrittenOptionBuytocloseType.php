<?php

namespace App\Form;

use App\Repository\StockRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;


class WrittenOptionBuytocloseType extends AbstractType
{

    public function __construct(Security $security, StockRepository $stockRepository)
    {
        $this->security = $security;
        $this->user = $this->security->getUser();
        $this->user_id = $this->user->getId();
        $this->stockRepository = $stockRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('option', HiddenType::class, array(
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('name', TextType::class, array(
                'label' => 'Option Name',
                'attr' => array(
                    'readonly' => true,
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00'
                ),
            ))
            ->add('contracts', NumberType::class, array(
                'label' => 'Number of Contracts',
                'attr' => array(
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => 0,
                    'x-on:click' => 'adjustContracts',
                    'x-bind' => 'contracts',
                )
            ))
            ->add('price', TextType::class, [
                'label' => 'Buyback Price',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00',
                ],
            ])
            ->add('stock_price', TextType::class, [
                'label' => 'Stock Price on Contract Close',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00',
                     'x-bind' => 'share_price',
                ],
            ])

            ->add('sell_shares', CheckboxType::class, [
                'label'    => 'Sell Shares as well?',
                'required' => false,
            ])

            ->add('number_of_shares', NumberType::class, [
                'label'    => 'Number of Shares',
                'attr' => array(
                    'id' => 'shares',
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => 0,
                    'x-bind' => 'shares',
                )
            ])

            ->add('payment_currency',ChoiceType::class,[
                'label' => 'Payment Currency',
                'choices' => array(
                    'CAN' => 'can',
                    'USD' => 'usd'
                ),
                'mapped' => false,
                'multiple' => false,
                'expanded' => true
            ])
            ->add('use_locked_funds', CheckboxType::class, [
                'label'    => 'Use Locked Funds?',
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
            // Configure your form options here
        ]);
    }
}

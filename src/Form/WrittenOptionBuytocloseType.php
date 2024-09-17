<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class WrittenOptionBuytocloseType extends AbstractType
{
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
                )
            ))
            ->add('price', TextType::class, [
                'label' => 'Buyback Price',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00'
                ],
            ])
            ->add('stock_price', TextType::class, [
                'label' => 'Stock Price',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                    'placeholder' => '$0.00'
                ],
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

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WalletLockedTransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currency', ChoiceType::class, [
                'label' => 'Currency',
                'choices'  => [
                    'Canadian Dollar' => "CAN",
                    'United States Dollar' => "USD",
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Transfer Type',
                'choices'  => [
                    'Unlock' => "unlock",
                    'Lock' => "lock",
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Amount',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('profit', CheckboxType::class, [
                'label' => 'Move % to Profit',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6']
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

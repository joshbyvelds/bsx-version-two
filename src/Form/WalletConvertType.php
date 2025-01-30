<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class WalletConvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add(
            'type', 
            ChoiceType::class, 
            [
                'label' => 'Conversion Type',
                'attr' => [
                    'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
                ],
                'choices' => [
                    'CDN to USD' => 5,
                    'USD to CDN' => 6,
                ],
                'multiple'=>false,
                'expanded' => true
            ]
        )
        ->add('usd', TextType::class, [
            'label' => 'Amount USD',
            'attr' => [
                'placeholder' => '$0.00',
                'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
            ],
        ])
        ->add('can', TextType::class, [
            'label' => 'Amount Canadian',
            'attr' => [
                'placeholder' => '$0.00',
                'class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6',
            ],
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

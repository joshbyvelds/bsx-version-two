<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                'choices' => [
                    'CDN to USD' => 5,
                    'USD to CDN' => 6,
                ],
                'expanded' => true
            ]
        )
        ->add('usd', IntegerType::class, ['label' => 'Amount USD'])
        ->add('can', IntegerType::class, ['label' => 'Amount Canadian'])
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

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class WalletAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('can', IntegerType::class, ['label' => 'Canadian Dollars', 'grouping' => true])
            ->add('usd', IntegerType::class, ['label' => 'United States Dollars','grouping' => true])
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

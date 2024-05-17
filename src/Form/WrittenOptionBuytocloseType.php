<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                'attr' => array(
                    'readonly' => true,
                ),
            ))
            ->add('contracts')
            ->add('price', TextType::class, [
                'attr' => ['placeholder' => '$0.00'],
            ])
            ->add('stock_price', TextType::class, [
                'attr' => ['placeholder' => '$0.00'],
            ])
            ->add('payment_currency',ChoiceType::class,[
                'choices' => array(
                    'CAN' => 'can',
                    'USD' => 'usd'
                ),
                'mapped' => false,
                'multiple'=>false,
                'expanded'=>true
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

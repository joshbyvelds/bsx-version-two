<?php

namespace App\Form;

use App\Entity\Dividend;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DividendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Stock', EntityType::class, [
                'class' => 'App\Entity\Stock',
                'choice_label' => 'name'
            ])
            ->add('payment_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('amount', TextType::class, [
                'attr' => ['placeholder' => '$0.00'],
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
            'data_class' => Dividend::class,
        ]);
    }
}

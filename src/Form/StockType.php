<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('ticker')
            ->add('country', ChoiceType::class, [
                'choices'  => [
                    'Canada' => "CAN",
                    'United States' => "USD",
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Blue Chip' => "BLUE",
                    'Red Chip' => "RED"
                ],
            ])
            ->add('pays_dividend', CheckboxType::class)
            ->add('save', SubmitType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'btn btn-primary float-right'
                ]    
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}

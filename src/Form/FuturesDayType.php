<?php

namespace App\Form;

use App\Entity\FuturesDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FuturesDayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount',  TextType::class, array('attr' => array('readonly' => true)))
            ->add('fees',    TextType::class, array('attr' => array('readonly' => true)))
            ->add('total',   TextType::class, array('attr' => array('readonly' => true)))
            ->add('trades',  TextType::class, array('attr' => array('readonly' => true)))
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
            'data_class' => FuturesDay::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dashboard_transactions')
            ->add('max_play_money')
            ->add('max_plays')
            ->add('stocks_update_sold_price', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_manual_update_enabled', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_disable_update_enabled', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_disable_canadian_update_enabled', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('futures_play_bucket_max')
            ->add('futures_profit_bucket_max')
            ->add('futures_weekly_goal')
            ->add('futures_use_split_profits', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('futures_profit_split_level_1_amount')
            ->add('futures_profit_split_level_1_ratio')
            ->add('futures_profit_split_level_2_amount')
            ->add('futures_profit_split_level_2_ratio')
            ->add('futures_profit_split_level_3_amount')
            ->add('futures_profit_split_level_3_ratio')
            ->add('futures_profit_split_level_4_amount')
            ->add('futures_profit_split_level_4_ratio')
            ->add('futures_profit_split_level_5_amount')
            ->add('futures_profit_split_level_5_ratio')
            ->add('futures_profit_split_level_6_amount')
            ->add('futures_profit_split_level_6_ratio')
            ->add('futures_profit_split_level_7_amount')
            ->add('futures_profit_split_level_7_ratio')
            ->add('futures_use_broker_margin', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('futures_broker_margin_amount')
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
            'data_class' => Settings::class,
            'validation_groups' => false,
        ]);
    }
}

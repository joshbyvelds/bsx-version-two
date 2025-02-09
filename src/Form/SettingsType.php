<?php

namespace App\Form;

use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', ChoiceType::class, [
                'choices'  => [
                    'Light' => 1,
                    'Dark' => 2,
                ],
            ])
            ->add('dashboard_transactions')
            ->add('dashboard_portfolio_large', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('dashboard_plays_panel',CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('dashboard_cc_panel',CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('weekly_total_value', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('dashboard_use_hot_cold_meter', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('dashboard_use_cash_equity_balance', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_update_sold_price', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('cash_equity_balance', TextType::class, ['label' => 'Cash - Equity Balance Minimum (Percentage)'])
            ->add('max_play_money')
            ->add('max_plays')
            ->add('stocks_update_sold_price', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_manual_update_enabled', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_disable_update_enabled', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_disable_canadian_update_enabled', CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('stocks_canadian_update_amount_limit')
            ->add('stocks_canadian_update_limit_timeout')
            ->add('futures_enabled')
            ->add('futures_data_fee')
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
            ->add('portfolio_update_on_weekend')
            ->add('stock_update_on_weekend')
            ->add('tfsa_limit')
            ->add('tfsa_enabled',CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('tfsa_track_balance',CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('fhsa_limit')
            ->add('fhsa_enabled',CheckboxType::class, ['mapped' => true, 'required' => false])
            ->add('rrsp_limit')
            ->add('rrsp_enabled',CheckboxType::class, ['mapped' => true, 'required' => false])

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

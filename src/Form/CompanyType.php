<?php

namespace App\Form;

use App\Entity\Company;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Company Name',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => 'Stock Company Name']
            ])
            ->add('ticker', TextType::class, [
                'label' => 'Company Ticker',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => 'ABCD']
            ])
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
            ->add('sector', EntityType::class, [
                'label' => 'Sector',
                'class' => 'App\Entity\Sector',
                'choice_label' => 'name',
            ])
            ->add('current_price', NumberType::class, [
                'label' => 'Current Price',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('price_yesterday', NumberType::class, [
                'label' => 'Yesterdays\'s Price',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('price_week', NumberType::class, [
                'label' => 'Last Week\'s Price',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('price_month', NumberType::class, [
                'label' => 'Last Month\'s Price',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('price_year', NumberType::class, [
                'label' => 'Last Years\'s Price',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('pays_dividend', checkboxType::class, [
                'label' => 'Pays Dividend',
                'required' => false,
            ])
            ->add('no_fee', checkboxType::class, [
                'label' => 'No Fee',
                'required' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}

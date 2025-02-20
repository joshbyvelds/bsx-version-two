<?php

namespace App\Form;

use App\Entity\WrittenOption;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class WrittenOptionRolloverType extends AbstractType
{
    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->user = $this->security->getUser();
        $this->user_id = $this->user->getId(); 
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contract_type',ChoiceType::class,[
                'label' => 'Contract Type',
                'choices' => array(
                    'Covered Call' => 1,
                    'Cash Secured Put' => 2
                ),
                'multiple'=>false,
            ])
            ->add('contracts', NumberType::class, ['label' => 'Contracts', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '0']])
            ->add('strike', NumberType::class, ['label' => 'Strike', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']])
            ->add('old_price', NumberType::class, ['label' => 'Old Option Buyback Price', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00'], 'mapped' => false])
            ->add('new_price', NumberType::class, ['label' => 'New Option Sell Price', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00'], 'mapped' => false])
            ->add('stock_buy_price', NumberType::class, ['label' => 'Stock Buy Price', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']])
            ->add('expiry', DateType::class, [
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6'],
                'label' => 'Expiry Date',
                'widget' => 'single_text',
            ])
            ->add('payment_currency',ChoiceType::class,[
                'label' => 'Payment Currency',
                'choices' => array(
                    'CAN' => 'can',
                    'USD' => 'usd'
                ),
                'mapped' => true,
                'multiple'=>false,
                'expanded'=>true
            ])
            ->add('total', TextType::class, ['mapped' => false, 'label' => 'Total Amount', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']])

            ->add('use_locked_funds',NumberType::class,[
                'label' => 'Use Locked Funds (if negative total)',
                'required' => false,
                'mapped' => false,
            ])

            ->add('payment_locked',NumberType::class,[
                'label' => 'Lock Payment? (for rollover) ',
                'required' => false,
                'mapped' => false,
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
            'data_class' => WrittenOption::class,
        ]);
    }
}

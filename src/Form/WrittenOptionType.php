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

class WrittenOptionType extends AbstractType
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
            ->add('Stock', EntityType::class, [
                'label' => 'Company Stock',
                'class' => 'App\Entity\Stock',
                // Traverse the relationship: Stock -> Company -> Name
                'choice_label' => 'company.name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        // Join the company table to prevent extra queries for each label
                        ->leftJoin('s.company', 'c')
                        ->addSelect('c')
                        ->where('s.user = :user')
                        ->setParameter('user', $this->user_id)
                        ->orderBy('c.name', 'ASC');
                },
            ])
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
            ->add('price', NumberType::class, ['label' => 'Price', 'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']])
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

            ->add('payment_locked',CheckboxType::class,[
                'label' => 'Lock Payment? (for rollover) ',
                'required' => false,
            ])

            ->add('part_of_play',CheckboxType::class,[
                'label' => 'Part of Play?',
                'required' => false,
                'mapped' => false,
            ])

            ->add('play', EntityType::class, [
                'attr' => [
                    'class' => 'block w-full rounded-md bg-white py-1.5 pl-3 pr-10 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body',
                ],
                'mapped' => false,
                'label' => 'Play',
                'class' => 'App\Entity\Play',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $user_id = $this->user_id;
                    return $er->createQueryBuilder('s')
                        ->where('s.User = :user')
                        ->setParameter('user', $user_id);
                },
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

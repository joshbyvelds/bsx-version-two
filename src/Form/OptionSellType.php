<?php

namespace App\Form;

use App\Entity\Option;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class OptionSellType extends AbstractType
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
            ->add('option', EntityType::class, [
                'class' => 'App\Entity\Option',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $user_id = $this->user_id;
                    return $er->createQueryBuilder('s')
                    ->where('s.user = :user')
                    ->andWhere('s.contracts > 0')
                    ->andWhere('s.expiry > :now') // Use a placeholder instead of NOW()
                    ->setParameter('user', $this->user_id)
                    ->setParameter('now', new \DateTime()); // Pass the current time here
                },
                'mapped' => false
            ])
            ->add('contracts', NumberType::class, [
                'mapped' => false,
                'label' => 'Contracts',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '0']
            ])
            ->add('average', NumberType::class, [
                'mapped' => false,
                'label' => 'Average',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '0']
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
            ->add('total', TextType::class, [
                'mapped' => false,
                'label' => 'Total',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '0']
            ])

            ->add('part_of_play', ChoiceType::class, [
                'label' => 'Part of Play?',
                'required' => false,
                'mapped' => false,
                'choices' => [
                    'Yes' => '1',
                    'No' => '0',
                ],
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
            'data_class' => Option::class,
        ]);
    }
}

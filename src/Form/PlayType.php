<?php

namespace App\Form;

use App\Entity\Play;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PlayType extends AbstractType
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
            ->add('name', TextType::class, [
                'label' => 'Play Name',
                'attr' => [
                    'placeholder' => 'Enter Play Name',
                ]
            ])
            ->add('Stock', EntityType::class, [
                'label' => 'Company Stock',
                'class' => 'App\Entity\Stock',
                'choice_attr' => function($stock) {
                    return [
                        'data-ticker' => $stock->getCompany()->getTicker(),
                        'data-stock-name' => $stock->getCompany()->getName(),
                        'data-id' => $stock->getId(),
                    ];
                },
                'choice_label' => function ($stock) {
                    // Accessing the nested company entity properties
                    return sprintf('(%s) %s', $stock->getCompany()->getTicker(), $stock->getCompany()->getName());
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->leftJoin('s.company', 'c')
                        ->addSelect('c')
                        ->where('s.user = :user')
                        ->setParameter('user', $this->user_id)
                        ->orderBy('c.name', 'ASC');
                },
            ])

            ->add('finished', checkboxType::class, [
                'label' => 'Play Finished',
            ])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6'],
                'label' => 'Start Date',
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6'],
                'label' => 'End Date',
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
            'data_class' => Play::class,
        ]);
    }
}

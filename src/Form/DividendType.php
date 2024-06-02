<?php

namespace App\Form;

use App\Entity\Dividend;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class DividendType extends AbstractType
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
                'class' => 'App\Entity\Stock',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $user_id = $this->user_id;
                    return $er->createQueryBuilder('s')
                    ->where('s.user = :user')
                    ->andWhere('s.pays_dividend = 1')
                    ->setParameter('user', $user_id);
                },
            ])
            ->add('payment_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('amount', TextType::class, [
                'attr' => ['placeholder' => '$0.00'],
            ])
            ->add('currency',ChoiceType::class,[
                'choices' => array(
                    'CAN' => 'can',
                    'USD' => 'usd'
                ),
                'multiple'=>false,
                'expanded'=>true
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

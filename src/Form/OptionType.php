<?php

namespace App\Form;

use App\Entity\Option;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class OptionType extends AbstractType
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
                    dump($user_id);
                    return $er->createQueryBuilder('s')
                    ->where('s.user = :user')
                    ->setParameter('user', $user_id);
                },
            ])
            ->add('type',ChoiceType::class,[
                'choices' => array(
                    'Call' => '1',
                    'Put' => '2'
                ),
            ])
            ->add('strike')
            ->add('contracts')
            ->add('average')
            ->add('expiry', DateType::class, [
                'widget' => 'single_text',
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
            ->add('cost', TextType::class, ['mapped' => false])
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

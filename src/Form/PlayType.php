<?php

namespace App\Form;

use App\Entity\Play;
use App\Entity\Stock;
use App\Entity\ShareBuy;
use App\Entity\Option;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $this->stock_id = 1; 
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Stock', EntityType::class, [
                'class' => Stock::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $user_id = $this->user_id;
                    dump($user_id);
                    return $er->createQueryBuilder('s')
                    ->where('s.user = :user')
                    ->setParameter('user', $user_id);
                },
            ])
            ->add('shares', EntityType::class, [
                'required' => false,
                'class' => ShareBuy::class,
                'choice_label' => function(ShareBuy $entity){
                    return $entity->getStock()->getName() . " - " . $entity->getDate()->format("F j, Y") . " - " . $entity->getAmount() . " * $" . number_format($entity->getPrice(), 2);
                },
                'multiple'=>true,
                'query_builder' => function (EntityRepository $er) {},
            ])
            ->add('options', EntityType::class, [
                'required' => false,
                'class' => Option::class,
                'choice_label' => function(Option $entity){
                    return $entity->getStock()->getName() .  " - " . $entity->getContracts() . " * $" . number_format($entity->getStrike(), 2) . (($entity->getType() === 1) ? "call" : "put") . " - " . $entity->getExpiry()->format("F j, Y");
                },
                'multiple'=>true,
                'query_builder' => function (EntityRepository $er) {},
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

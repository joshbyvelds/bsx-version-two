<?php

namespace App\Form;

use App\Entity\Play;
use App\Entity\ShareBuy;
use App\Entity\Stock;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TransferSharesType extends AbstractType
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
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])
            ->add('stock', EntityType::class, [
                'required' => true,
                'class' => Stock::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $user_id = $this->user_id;
                    return $er->createQueryBuilder('s')
                        ->where('s.user = :user')
                        ->setParameter('user', $user_id);
                },
            ])
            ->add('shareBuy', EntityType::class, [
                'required' => true,
                'class' => ShareBuy::class,
                'choice_label' => function(ShareBuy $entity){
                    return $entity->getStock()->getName() . " - " . $entity->getDate()->format("F j, Y") . " - " . $entity->getAmount() . " * $" . number_format($entity->getPrice(), 2);
                },
                'query_builder' => function (EntityRepository $er) {},
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'Amount',
                'attr' => ['class' => 'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-inset ring-1 ring-gray-300 text-sm leading-6', 'placeholder' => '$0.00']
            ])
            ->add('last_shares', CheckboxType::class, [
                'required' => false,
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

    }
}

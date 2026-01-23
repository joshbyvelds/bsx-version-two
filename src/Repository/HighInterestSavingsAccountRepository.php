<?php

namespace App\Repository;

use App\Entity\HighInterestSavingsAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HighInterestSavingsAccount>
 *
 * @method HighInterestSavingsAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method HighInterestSavingsAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method HighInterestSavingsAccount[]    findAll()
 * @method HighInterestSavingsAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HighInterestSavingsAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HighInterestSavingsAccount::class);
    }

    public function add(HighInterestSavingsAccount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HighInterestSavingsAccount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HighInterestSavingsAccount[] Returns an array of HighInterestSavingsAccount objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HighInterestSavingsAccount
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\TenPercentPlanWeek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TenPercentPlanWeek>
 *
 * @method TenPercentPlanWeek|null find($id, $lockMode = null, $lockVersion = null)
 * @method TenPercentPlanWeek|null findOneBy(array $criteria, array $orderBy = null)
 * @method TenPercentPlanWeek[]    findAll()
 * @method TenPercentPlanWeek[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TenPercentPlanWeekRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TenPercentPlanWeek::class);
    }

    public function add(TenPercentPlanWeek $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TenPercentPlanWeek $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TenPercentPlanWeek[] Returns an array of TenPercentPlanWeek objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TenPercentPlanWeek
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

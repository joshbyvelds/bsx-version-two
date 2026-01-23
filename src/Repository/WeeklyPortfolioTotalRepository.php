<?php

namespace App\Repository;

use App\Entity\WeeklyPortfolioTotal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeeklyPortfolioTotal>
 *
 * @method WeeklyPortfolioTotal|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeeklyPortfolioTotal|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeeklyPortfolioTotal[]    findAll()
 * @method WeeklyPortfolioTotal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeeklyPortfolioTotalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyPortfolioTotal::class);
    }

    public function add(WeeklyPortfolioTotal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeeklyPortfolioTotal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return WeeklyPortfolioTotal[] Returns an array of WeeklyPortfolioTotal objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?WeeklyPortfolioTotal
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\FuturesBuckets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FuturesBuckets>
 *
 * @method FuturesBuckets|null find($id, $lockMode = null, $lockVersion = null)
 * @method FuturesBuckets|null findOneBy(array $criteria, array $orderBy = null)
 * @method FuturesBuckets[]    findAll()
 * @method FuturesBuckets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FuturesBucketsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FuturesBuckets::class);
    }

    public function add(FuturesBuckets $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FuturesBuckets $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FuturesBuckets[] Returns an array of FuturesBuckets objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FuturesBuckets
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

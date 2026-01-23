<?php

namespace App\Repository;

use App\Entity\WrittenOptionRollover;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WrittenOptionRollover>
 *
 * @method WrittenOptionRollover|null find($id, $lockMode = null, $lockVersion = null)
 * @method WrittenOptionRollover|null findOneBy(array $criteria, array $orderBy = null)
 * @method WrittenOptionRollover[]    findAll()
 * @method WrittenOptionRollover[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WrittenOptionRolloverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WrittenOptionRollover::class);
    }

    public function add(WrittenOptionRollover $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WrittenOptionRollover $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return WrittenOptionRollover[] Returns an array of WrittenOptionRollover objects
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

//    public function findOneBySomeField($value): ?WrittenOptionRollover
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

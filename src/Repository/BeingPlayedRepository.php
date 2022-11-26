<?php

namespace App\Repository;

use App\Entity\BeingPlayed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BeingPlayed>
 *
 * @method BeingPlayed|null find($id, $lockMode = null, $lockVersion = null)
 * @method BeingPlayed|null findOneBy(array $criteria, array $orderBy = null)
 * @method BeingPlayed[]    findAll()
 * @method BeingPlayed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BeingPlayedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BeingPlayed::class);
    }

    public function add(BeingPlayed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BeingPlayed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return BeingPlayed[] Returns an array of BeingPlayed objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BeingPlayed
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

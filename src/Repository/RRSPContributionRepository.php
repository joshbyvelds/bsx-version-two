<?php

namespace App\Repository;

use App\Entity\RRSPContribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RRSPContribution>
 *
 * @method RRSPContribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method RRSPContribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method RRSPContribution[]    findAll()
 * @method RRSPContribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RRSPContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RRSPContribution::class);
    }

    public function add(RRSPContribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RRSPContribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RRSPContribution[] Returns an array of RRSPContribution objects
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

//    public function findOneBySomeField($value): ?RRSPContribution
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

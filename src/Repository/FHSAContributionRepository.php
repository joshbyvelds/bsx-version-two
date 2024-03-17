<?php

namespace App\Repository;

use App\Entity\FHSAContribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FHSAContribution>
 *
 * @method FHSAContribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method FHSAContribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method FHSAContribution[]    findAll()
 * @method FHSAContribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FHSAContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FHSAContribution::class);
    }

    public function add(FHSAContribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FHSAContribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FHSAContribution[] Returns an array of FHSAContribution objects
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

//    public function findOneBySomeField($value): ?FHSAContribution
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Match2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Match2|null find($id, $lockMode = null, $lockVersion = null)
 * @method Match2|null findOneBy(array $criteria, array $orderBy = null)
 * @method Match2[]    findAll()
 * @method Match2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Match2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Match2::class);
    }

    // /**
    //  * @return Match2[] Returns an array of Match2 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Match2
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

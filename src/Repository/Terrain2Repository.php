<?php

namespace App\Repository;

use App\Entity\Terrain2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Terrain2|null find($id, $lockMode = null, $lockVersion = null)
 * @method Terrain2|null findOneBy(array $criteria, array $orderBy = null)
 * @method Terrain2[]    findAll()
 * @method Terrain2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Terrain2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Terrain2::class);
    }

    // /**
    //  * @return Terrain2[] Returns an array of Terrain2 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Terrain2
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

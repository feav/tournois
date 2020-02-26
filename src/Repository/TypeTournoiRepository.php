<?php

namespace App\Repository;

use App\Entity\TypeTournoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TypeTournoi|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeTournoi|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeTournoi[]    findAll()
 * @method TypeTournoi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeTournoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeTournoi::class);
    }

    // /**
    //  * @return TypeTournoi[] Returns an array of TypeTournoi objects
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
    public function findOneBySomeField($value): ?TypeTournoi
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

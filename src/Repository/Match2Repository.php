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
        $this->em = $this->getEntityManager()->getConnection();
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

    public function getMatchByEtat($idTournoi, $currentTour, $nbrTerrain=null){
        $sql = "
            SELECT id FROM match2 
                WHERE  tournoi_id = :idTournoi
                AND num_tour = :currentTour
                AND ( etat = 'en_cours' OR etat = 'en_attente') ";
        if(!is_null($nbrTerrain))
            $sql .= " LIMIT $nbrTerrain";
        
        $posts = $this->em->prepare($sql);
        $posts->execute(['idTournoi'=>$idTournoi, 'currentTour'=> $currentTour]);
        $posts = $posts->fetchAll();

        $postsArray = [];
        foreach ($posts as $key => $value) {
            $qb = $this->createQueryBuilder('match')
                ->Where('match.id = :id')
                ->setParameter('id', $value['id']);
            $postsArray[] = $qb->getQuery()->getOneOrNullResult();
        }
        return $postsArray;
    }
}

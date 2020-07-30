<?php

namespace App\Repository;

use App\Entity\Tournoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Tournoi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournoi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournoi[]    findAll()
 * @method Tournoi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournoi::class);
        $this->em = $this->getEntityManager()->getConnection();
    }

    // /**
    //  * @return Tournoi[] Returns an array of Tournoi objects
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
    public function findOneBySomeField($value): ?Tournoi
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getEmailJoueurTournois($tournoi_id){
        $sql = "
            SELECT j.nom, j.email FROM joueur as j
                inner join equipe as eq
                inner join tournoi as tnoi
                WHERE  j.equipe_id = eq.id
                AND eq.tournoi_id = tnoi.id
                AND tnoi.id = :tournoi_id
                AND j.email IS NOT NULL";
        $posts = $this->em->prepare($sql);
        $posts->execute(['tournoi_id' => $tournoi_id]);
        $posts = $posts->fetchAll();
        $postsArray = [];
        foreach ($posts as $key => $value) {
            $postsArray[$value['email']] = $value['nom'];
        }
        return $postsArray;
    }
}

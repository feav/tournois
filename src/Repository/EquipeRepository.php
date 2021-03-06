<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Equipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipe[]    findAll()
 * @method Equipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
        $this->em = $this->getEntityManager()->getConnection();
    }

    // /**
    //  * @return Equipe[] Returns an array of Equipe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Equipe
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getNbrEquipeQualifie($idTournoi){
        $sql = "
            SELECT COUNT(*) as nbr FROM equipe 
                WHERE  equipe.en_competition = :etat
                AND equipe.tournoi_id = :idTournoi";
        $nbrEquipe = $this->em->prepare($sql);
        $nbrEquipe->execute(['etat'=>1, 'idTournoi'=> $idTournoi]);
        $nbrEquipe = $nbrEquipe->fetch();

        return $nbrEquipe['nbr'];
    }
}

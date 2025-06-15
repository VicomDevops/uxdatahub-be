<?php

namespace App\Repository;

use App\Entity\FaceShot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FaceShot|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaceShot|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaceShot[]    findAll()
 * @method FaceShot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaceShotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaceShot::class);
    }

    // /**
    //  * @return FaceShot[] Returns an array of FaceShot objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FaceShot
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

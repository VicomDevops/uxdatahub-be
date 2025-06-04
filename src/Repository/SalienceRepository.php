<?php

namespace App\Repository;

use App\Entity\Salience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Salience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salience[]    findAll()
 * @method Salience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salience::class);
    }

    // /**
    //  * @return Salience[] Returns an array of Salience objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Salience
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

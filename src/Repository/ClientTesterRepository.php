<?php

namespace App\Repository;

use App\Entity\ClientTester;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientTester|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTester|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTester[]    findAll()
 * @method ClientTester[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTesterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientTester::class);
    }

    // /**
    //  * @return ClientTester[] Returns an array of ClientTester objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClientTester
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

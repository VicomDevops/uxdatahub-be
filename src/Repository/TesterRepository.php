<?php

namespace App\Repository;

use App\Entity\Tester;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tester|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tester|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tester[]    findAll()
 * @method Tester[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TesterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tester::class);
    }

    public function getNewTesters()
    {
        return $this->createQueryBuilder('t')
            ->select(
            't.id',
            't.isActive',
            't.dateOfBirth',
            't.gender',
            't.email',
            't.createdAt',
            't.name',
            't.lastname',
            't.state'
        )
            ->andWhere('t.state = :state')
            ->setParameter('state', 'to_contact')
            ->andWhere('t.isActive = :active')
            ->setParameter('active', 'false')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Tester[] Returns an array of Tester objects
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
    public function findOneBySomeField($value): ?Tester
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getRandomTester($id){
        return $this->createQueryBuilder('t')
            ->select(
                't.id',
                't.isActive',
                't.email',
                't.createdAt',
                't.name',
                't.lastname',
                't.state'
            )
            ->Where('t.id != :id')
            ->setParameter('id', $id)
            ->andWhere('t.isActive = :state')
            ->setParameter('state', true)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

    }

    public function getRandomTestersByFiltres(array $sql,Int $nombreTesteurs)
    {
        $q=$this->createQueryBuilder('t');
            foreach($sql as $key=>$value){
                    $q->andWhere("t.$key = :$key");
                    $q->setParameter("$key", $value);
            }

        return
            $q->andWhere('t.isActive = :state')
            ->setParameter('state', true)
            ->getQuery()
            ->setMaxResults($nombreTesteurs*5)
            ->getResult();

    }
}

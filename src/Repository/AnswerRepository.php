<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Scenario;
use App\Entity\Step;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

//    public function countGroupByAnswers(Scenario $scenario)
//    {
//       return $this->createQueryBuilder('a')
//           ->join('a.step', 'step')
//           ->andWhere('step.scenario = :scenario')
//           ->setParameter('scenario', $scenario)
//           ->select('a.answer, COUNT(a) AS count')
//           ->groupBy('a.answer')
//           ->getQuery()
//           ->getResult();
//    }
    public function countGroupByAnswers(Step $step)
    {
        return $this->createQueryBuilder('a')
            ->select('a.answer', 'a.comment','clientTester.id AS testerId','clientTester.name AS TesterName, clientTester.lastname AS TesterLastName', 'COUNT(a) AS count')
            ->leftJoin('a.clientTester', 'clientTester')
            ->andWhere('a.step = :step')
            ->setParameter('step', $step)
            ->groupBy('a.answer', 'a.comment','clientTester.id','clientTester.name','clientTester.lastname')
            ->orderBy('clientTester.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getScoresByStep(Step $step)
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.step', 'step')
            ->andWhere('a.step = :step')
            ->setParameter('step', $step)
            ->select('a.score');

        return $qb->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Answer[] Returns an array of Answer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Answer
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

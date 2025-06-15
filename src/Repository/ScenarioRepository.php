<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\ClientTester;
use App\Entity\Panel;
use App\Entity\Scenario;
use App\Entity\Tester;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Scenario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scenario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scenario[]    findAll()
 * @method Scenario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScenarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scenario::class);
    }

    public function findAllScenarios()
    {
        return $this->createQueryBuilder('scenario')
                ->join('scenario.steps', 'step')
                ->addSelect('step')
                ->orderBy('step.id', 'ASC')
                ->getQuery()->getResult();
    }

    public function findScenariosByClientTester(ClientTester $tester)
    {
        return $this->createQueryBuilder('s')
            ->join('s.panel', 'panel')
            ->join('panel.clientTesters', 'clientTesters')
            ->join('s.steps', 'step')
            ->join('App\Entity\Test', 'test', 'WITH', 'test.scenario = s AND test.clientTester = :clientTester')
            ->andWhere('clientTesters.id = :clientTester')
            ->setParameter('clientTester', $tester)
            ->andWhere('s.etat IN (:etats)')
            ->setParameter('etats', [3, 6]) // Check for either etat = 3 or etat = 6
            ->orderBy('s.createdAt', 'DESC')
            ->addOrderBy('step.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findScenariosByTester(Tester $tester)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.panel', 'panel')
            ->join('panel.insightTesters', 'insightTesters')
            ->andWhere('insightTesters.id = :insightTester')
            ->setParameter('insightTester', $tester)
            ->andWhere('s.etat = :etat')
            ->setParameter('etat', 3)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $q;
    }


    public function findByClientAndProgress($user)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.client = :client')
            ->andWhere('s.progress != 0')
            ->setParameter('client', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    public function getTestsInfos(Scenario $scenario)
//    {
//        return $this->createQueryBuilder('s')
//            ->select('s.id', 's.title')
//            ->addSelect('(SELECT COUNT(DISTINCT a.clientTester) FROM App\Entity\Answer a JOIN a.step st3 WHERE st3.scenario = s.id) AS testersDone')
//            ->addSelect('(SELECT COUNT(t.id) FROM App\Entity\Test t WHERE t.scenario = s.id) AS testers')
//            ->addSelect('(SELECT COUNT(st.id) FROM App\Entity\Step st WHERE st.scenario = s.id) AS steps')
//            ->addSelect('(SELECT DISTINCT p.type FROM App\Entity\Panel p WHERE s.panel = p.id) AS type')
//            ->addSelect('(SELECT AVG(st2.average) FROM App\Entity\Step st2 WHERE st2.scenario = s.id) AS score')
//            ->andWhere('s.id = :id')
//            ->setParameter('id', $scenario->getId())
//            ->groupBy('s.id')
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

    public function findAllScenariosTestedAtLeastOne(Client $client)
    {
        return $this->createQueryBuilder('s')
            ->select('s.title','s.isTested AS testersNumber')
            ->andWhere('s.client = :client')
            ->setParameter('client', $client)
            ->andWhere('s.isTested = 1 ')
            ->orderBy('s.title','ASC')
            ->getQuery()
            ->getResult();
    }

    
    public function findScenariosClosedByTester(Tester $tester)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.panel', 'panel')
            ->join('panel.insightTesters', 'insightTesters')
            ->andWhere('insightTesters.id = :insightTester')
            ->setParameter('insightTester', $tester)
            ->andWhere('s.etat = 4')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $q;
    }

    public function findScenariosClosedByClientTester(ClientTester $clientTester)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.panel', 'panel')
            ->join('panel.clientTesters', 'clientTesters')
            ->andWhere('clientTesters.id = :clientTester')
            ->setParameter('clientTester', $clientTester)
            ->andWhere('s.etat = 4')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $q;
    }
}

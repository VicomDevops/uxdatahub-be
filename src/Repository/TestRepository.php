<?php

namespace App\Repository;

use App\Entity\ClientTester;
use App\Entity\Panel;
use App\Entity\Scenario;
use App\Entity\Test;
use App\Entity\Tester;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @method Test|null find($id, $lockMode = null, $lockVersion = null)
 * @method Test|null findOneBy(array $criteria, array $orderBy = null)
 * @method Test[]    findAll()
 * @method Test[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Test::class);
    }

    /**
     * @return Test[] Returns an array of Test objects
     */
    public function findTestsByState($tester, $value = "")
    {
        $q = $this->createQueryBuilder('t')
            ->andWhere('t.tester = : tester')
            ->setParameter('tester', $tester);
        if ($value) {

            $q->andWhere('t.state = :state')
                ->setParameter('state', $value);
        }

        return $q->getQuery()
            ->getResult();
    }

    public function findTestsByTesterAndScenario(User $tester, Scenario $scenario)
    {
        $q = $this->createQueryBuilder('t');
        if ($tester instanceof Tester) {
            $q->join('t.tester', 'tester');
        } else if ($tester instanceof ClientTester) {
            $q->join('t.clientTester', 'tester');
        }
        $q->andWhere('t.scenario = :scenario')
            ->andWhere('tester = :tester')
            ->setParameter('tester', $tester)
            ->setParameter('scenario', $scenario->getId());
        return $q->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Test
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
//    /**
//     * @return Test Returns a Test object
//     */

}

<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\SubClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubClient[]    findAll()
 * @method SubClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubClient::class);
    }

    public function getAllByClient(Client $client)
    {
        return $this->createQueryBuilder('sc')
            ->select(
                'sc.id',
                'sc.createdAt',
                'sc.name',
                'sc.lastname',
                'sc.roles',
                'sc.state',
                'sc.isActive',
                'sc.username',
                'sc.writeRights'
            )
            ->where('sc.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();
    }
}

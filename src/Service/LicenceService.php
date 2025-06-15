<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Contract;
use App\Entity\LicenceCategory;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;

class LicenceService
{
    private $entityManager;
    private $contractRepository;

    public function __construct(EntityManagerInterface $entityManager, ContractRepository $contractRepository)
    {
        $this->entityManager = $entityManager;
        $this->contractRepository = $contractRepository;
    }

    public function addContractToClient(Client $client, LicenceCategory $licenceType)
    {
        if (count($client->getContracts()) == 0) {
            $contract = new Contract();
            $date = new \Datetime('+30 days');
            $contract->setStartAt(new \DateTime())
                ->setEndAt($date)
                ->setLicenceCategory($licenceType)
                ->setClient($client);
            $this->entityManager->persist($contract);
            $this->entityManager->flush();
        }
        return 1;
    }
}

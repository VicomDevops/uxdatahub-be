<?php

namespace App\Service;

use App\Repository\AdminRepository;
use App\Repository\ClientRepository;
use App\Repository\SubClientRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SubClientService
{

    private $serializer;

    private $entityManager;
    private $security;
    private $tokenStorage;

    private $responseService;
    private $clientRepository;
    private $paramsHelper;
    private $passwordGenerator;
    private $mailer;
    private $subClientRepository;


    public function __construct(Mailer $mailer,PasswordGenerator $passwordGenerator,ParamsHelper $paramsHelper,ResponseService $responseService,ClientRepository $clientRepository,TokenStorageInterface $tokenStorage,SubClientRepository $subClientRepository,SerializerInterface $serializer,EntityManagerInterface $entityManager)
    {
        $this->serializer  = $serializer;
        $this->entityManager = $entityManager;
        $this->subClientRepository = $subClientRepository;
        $this->clientRepository = $clientRepository;
        $this->tokenStorage = $tokenStorage;
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->passwordGenerator = $passwordGenerator;
        $this->mailer = $mailer;
    }

    public function removeSubClient()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $subClient = $this->subClientRepository->findOneBy(['id' => $inputs["subclient_id"]]);
            if (!$subClient)
            {
                return $this->responseService->getResponseToClient(null,200,"admin.subclient_not_found");
            }
            $this->entityManager->remove($subClient);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
        }
    }

}
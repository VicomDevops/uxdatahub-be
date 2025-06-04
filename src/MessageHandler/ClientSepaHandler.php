<?php

namespace App\MessageHandler;

use App\Message\ClientSepaMessage;
use App\Repository\ClientRepository;
use App\Service\PaiementService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ClientSepaHandler implements MessageHandlerInterface
{
    private $paiementService;
    private $clientRepository;
        public function __construct(PaiementService $paiementService,ClientRepository $clientRepository){
        $this->paiementService = $paiementService;
        $this->clientRepository = $clientRepository;
    }
    public function __invoke(ClientSepaMessage $message)
    {
        $clientId = $message->getClient();
        $client = $this->clientRepository->findOneBy(['id'=>$clientId]);
        if($client!= null ){
            $this->paiementService->sepaPaiement($client);
        }
    }

}
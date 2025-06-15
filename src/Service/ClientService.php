<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Entity\Client;
use App\Repository\AdminRepository;
use App\Repository\ClientRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AllowDynamicProperties] class ClientService
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
    private $filesystem;
    private $messageBus;
    private $parameterBag;
    private $translator;


    public function __construct(TranslatorInterface $translator,ParameterBagInterface $parameterBag,Filesystem $filesystem,Mailer $mailer,PasswordGenerator $passwordGenerator,ParamsHelper $paramsHelper,ResponseService $responseService,ClientRepository $clientRepository,TokenStorageInterface $tokenStorage,AdminRepository $adminRepository,SerializerInterface $serializer,EntityManagerInterface $entityManager,MessageBusInterface $messageBus)
    {
        $this->serializer  = $serializer;
        $this->entityManager = $entityManager;
        $this->adminRepository = $adminRepository;
        $this->clientRepository = $clientRepository;
        $this->tokenStorage = $tokenStorage;
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->passwordGenerator = $passwordGenerator;
        $this->mailer = $mailer;
        $this->filesystem = $filesystem;
        $this->messageBus = $messageBus;
        $this->parameterBag = $parameterBag;
        $this->translator = $translator;
    }

    public function SignUpClients()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $client = $this->serializer->deserialize(json_encode($inputs), Client::class, 'json');
            $token = bin2hex(random_bytes(32));
            $client->setIsActive(false)
                ->setRoles(['ROLE_CLIENT'])
                ->setState('user_ok')
                ->setConfirmationToken($token);
            $URL = $this->generateConfirmationAccountUrl($token);
            $password = $this->passwordGenerator->newPassword($client);
            $this->entityManager->persist($client);
            $this->entityManager->flush();
            $message = $this->translator->trans('users.active_account',
                [
                    'NUMBER' => 2
                ]);
            $this->mailer->validateClient($client, $password);
            $this->mailer->confirmClientAccount($client, $URL);

            return $this->responseService->getResponseToClient($message);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function setClientAccountToConfirmed()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $client = $this->entityManager->getRepository(Client::class)->findOneBy(['confirmationToken' => $inputs['token']]);
            if (!$client){
                return $this->responseService->getResponseToClient(null,201,"client.token_or_client_not_fount");
            }
            $client->setIsVerified(true)
                    ->setConfirmationToken('');
            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient(null,200, "users.active_account_success");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
        }
    }

    private function generateConfirmationAccountUrl($token):string {
        return $this->parameterBag->get('FRONT_URL')."/confirm/client/account"."?token=".$token;
        }

}
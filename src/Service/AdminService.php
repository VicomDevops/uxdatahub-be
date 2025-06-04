<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Entity\Answer;
use App\Entity\Client;
use App\Entity\Scenario;
use App\Entity\Test;
use App\Message\VideoAnalyzeMessage;
use App\Repository\AdminRepository;
use App\Repository\ClientRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Filesystem\Filesystem;

class AdminService
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


    public function __construct(Filesystem $filesystem,Mailer $mailer,PasswordGenerator $passwordGenerator,ParamsHelper $paramsHelper,ResponseService $responseService,ClientRepository $clientRepository,TokenStorageInterface $tokenStorage,AdminRepository $adminRepository,SerializerInterface $serializer,EntityManagerInterface $entityManager,MessageBusInterface $messageBus)
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
    }

    public function getListAdmins()
    {
        $user = $this->getCurrentUser()->getUser();
        $admins = $this->adminRepository->getAllAdmins($user->getEmail());
        $data = $this->serializer->serialize($admins, 'json');
        $this->responseService->getResponseToClient($data);
        return new Response($data, Response::HTTP_OK);
    }

    public function getNewClientsList()
    {
        try {
            $newClients = $this->clientRepository->getNewClients();
            $response = $this->serializer->serialize($newClients, 'json');

            return $this->responseService->getResponseToClient(json_decode($response,true));

        }catch(\Exception $exception)
        {
            return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
        }
    }

    public function validateClient()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $client = $this->clientRepository->findOneBy(['id' => $inputs["client_id"]]);
            if (!$client)
            {
                return $this->responseService->getResponseToClient(null,200,"admin.client_not_found");
            }
            if($client->getState()==='to_contact'){
                try {
                    $password = $this->passwordGenerator->newPassword($client);
                    $client->setState('user_ok');
                    $this->mailer->validateClient($client, $password);
                    $this->entityManager->flush();

                    return $this->responseService->getResponseToClient(null,200,"admin.client_validation_success");

                } catch (\Exception $exception) {

                    return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
                }
            }else{
                return $this->responseService->getResponseToClient(null,201,"client.client_already_validated");
            }
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
        }
    }

    public function removeAdmin()
    {
        try {
        $inputs = $this->paramsHelper->getInputs();
        $admin = $this->adminRepository->findOneBy(['id' => $inputs["admin_id"]]);
        if (!$admin)
        {
            return $this->responseService->getResponseToClient(null,200,"admin.admin_not_found");
        }
            $this->entityManager->remove($admin);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
        return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
        }
    }

    public function resetScenariosForTesters()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $test = $this->entityManager->getRepository(Test::class)->findOneBy(['clientTester' => $inputs["tester_id"],"scenario" => $inputs["scenario_id"]]);
            if (!$test)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $answers = $this->entityManager->getRepository(Answer::class)->findBy(['clientTester' => $inputs["tester_id"],"test" => $test->getId()]);
            foreach($answers as $answer)
            {
                if ($answer->getVideo() != null && $this->filesystem->exists($answer->getVideo()))
                {
                    $this->filesystem->remove($answer->getVideo());
                }
                $this->entityManager->remove($answer);
                $this->entityManager->flush();
            }
            $test->setEtat(1);
            $test->setIsAnalyzed(false);
            $this->entityManager->persist($test);
            $this->entityManager->persist($scenario);
            $this->entityManager->flush();

            $this->mailer->resetScenarioNotification($test->getClientTester(),$scenario);

            return $this->responseService->getResponseToClient();


        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,$exception->getMessage());
        }
    }

    public function submitReanalyzeTest()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $test = $this->entityManager->getRepository(Test::class)->findOneBy(["id" => $inputs["test_id"]]);
            $test->setEtat(2);
            $dispatch = $this->messageBus->dispatch(new VideoAnalyzeMessage($test->getId()));
            $this->responseService->getResponseToClient(['Dispatch video' => $dispatch]);

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,$exception->getMessage());
        }
    }

    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }

}
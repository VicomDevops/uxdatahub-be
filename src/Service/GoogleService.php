<?php

namespace App\Service;

use App\Entity\ClientTester;
use App\Entity\Scenario;
use App\Entity\Tester;
use App\Entity\User;
use App\Repository\PanelRepository;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;


class GoogleService
{

    private $entityManager;
    private $scenarioRepository;
    private $serializer;
    private $testRepository;
    private $responseService;
    private $tokenStorage;
    private $normalizer;
    private $paramsHelper;
    private $analyzeService;
    private $mailer;

    public function __construct(Mailer $mailer,AnalyzeService $analyzeService,ParamsHelper $paramsHelper ,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, ScenarioRepository $scenarioRepository, SerializerInterface $serializer,TestRepository $testRepository)
    {
        $this->entityManager = $entityManager;
        $this->scenarioRepository = $scenarioRepository;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->paramsHelper = $paramsHelper;
        $this->analyzeService = $analyzeService;
    }

    public function googleAnalyzePerTesterAndScenarioList()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->entityManager->getRepository(User::class)->findOneBy(["id" => $inputs["tester_id"]]);
                if (!$user)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'users.not_found');
                }
            $test = null;
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs["scenario_id"]]);
                if (!$scenario)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
                }
            if ($user instanceof Tester) {
                $test = $this->testRepository->findOneBy(["tester" => $user, "scenario" => $scenario]);

            } else if($user instanceof ClientTester) {
                $test = $this->testRepository->findOneBy(["clientTester" => $user, "scenario" => $scenario]);

            }
                if (!$test)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
                }
            $tester = $this->serializer->serialize($test, 'json', ['groups' => 'google_tester']);
            $response = $this->analyzeService->getAverageScoreDuration($tester);

            return $this->responseService->getResponseToClient(json_decode($response,true));
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception,500,"general.500");
        }

    }

    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }

}
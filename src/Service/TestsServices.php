<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\ClientTester;
use App\Entity\Test;
use App\Repository\PanelRepository;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\VideoUploadMessage;




class TestsServices
{
    private $entityManager;
    private $scenarioRepository;
    private $serializer;
    private $testRepository;
    private $mathematicalFunctionsService;
    private $responseService;
    private $tokenStorage;
    private $normalizer;
    private $paramsHelper;
    private $analyzeService;
    private $parameterBag;
    private $messageBus;
    private $containerBag;

    public function __construct(ContainerBagInterface $containerBag,MessageBusInterface $messageBus,ParameterBagInterface $parameterBag,AnalyzeService $analyzeService,ParamsHelper $paramsHelper,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, ScenarioRepository $scenarioRepository, SerializerInterface $serializer,TestRepository $testRepository, MathematicalFunctionsService $mathematicalFunctionsService)
    {
        $this->entityManager = $entityManager;
        $this->scenarioRepository = $scenarioRepository;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->mathematicalFunctionsService = $mathematicalFunctionsService;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->paramsHelper = $paramsHelper;
        $this->analyzeService = $analyzeService;
        $this->parameterBag = $parameterBag;
        $this->messageBus = $messageBus;
        $this->containerBag = $containerBag;
    }

    public function setTestToStartStatus()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->getCurrentUser()->getUser();
            if($user instanceof ClientTester){
                $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["id"]]);
                if (!$scenario)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
                }
                $test = $this->testRepository->findOneBy(['clientTester' => $user, 'scenario' => $scenario->getId()]);
                if (!$test)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
                }
                if(isset($test) and $test->getEtat()==0 OR $test->getEtat()==null){
                    $test->setStartedAt(new \DateTime());
                    $this->entityManager->persist($test);
                    $this->entityManager->flush();

                }else{
                    return $this->responseService->getResponseToClient(null,201,'test.already_start');
                }

                return $this->responseService->getResponseToClient(null,200,'test.start');
            }else
            {
                return $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }


    public function setUploadFile()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $answer = $this->entityManager->getRepository(Answer::class)->findOneBy(["id" => $inputs["answer_id"]]);
            if (!$answer)
            {
                return $this->responseService->getResponseToClient(null, 404, 'responses.not_found');
            }
            $file = file_get_contents($inputs["file"]);
            $dispatch = $this->messageBus->dispatch(new VideoUploadMessage($file,$inputs["test_id"],$inputs["answer_id"],$inputs["duration"]));
            return $this->responseService->getResponseToClient([
                "test_id" => $dispatch->getMessage()->getTestId(),
                "answer_id" => $dispatch->getMessage()->getAnswerId()
            ]);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.exception');
        }
    }

    public function asychUpload($currentfile,$test_id,$answer_id,$duration)
    {
        try {
            $this->responseService->getResponseToClient("uploading start");
            $destinationDirectory = $this->parameterBag->get('video_path');
            $answer = $this->entityManager->getRepository(Answer::class)->findOneBy(["id" => $answer_id]);
            $filename = $test_id.'_'.$answer->getStep()->getId().'_'.$answer_id.".mkv";
            $destination = $destinationDirectory.'/'.$filename;
            $sourcePath = $destination;
            file_put_contents($sourcePath, $currentfile,FILE_APPEND);
            $answer->setVideo($destination);
            $answer->setDuration($duration);
            $this->entityManager->persist($answer);
            $this->entityManager->flush();
            return true;

        }catch (\Exception $exception)
        {
            $this->responseService->getResponseToClient($exception->getMessage());
            return  $exception->getMessage();
        }
    }

    public function setInterruptedTest()
    {
        try {
            if (!$this->getCurrentUser()->getUser() instanceof ClientTester)
            {
                return  $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }
            $inputs = $this->paramsHelper->getInputs();
            $test = $this->entityManager->getRepository(Test::class)->findOneBy(["id" => $inputs["test_id"],"clientTester" => $this->getCurrentUser()->getUser()]);
            if (!$test)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $this->entityManager->beginTransaction();
            $test->setIsInterrupted(true);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->responseService->getResponseToClient();


        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.exception');
        }
    }

    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }

}
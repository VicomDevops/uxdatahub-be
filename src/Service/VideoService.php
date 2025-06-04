<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\FaceShot;
use App\Entity\Step;
use App\Entity\Test;
use App\Dto\AnswersDto;
use App\Message\VideoAnalyzeMessage;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Utils\ParamsHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use function PHPUnit\Framework\isFalse;


class VideoService
{
    private TestRepository $testRepository;
    private EntityManagerInterface $entityManager;
    private $uploadVideo;
    private MessageBusInterface $messageBus;
    private $videoAnalyze;
    private ResponseService $responseService;
    private ParamsHelper $paramsHelper;
    private TokenStorageInterface $tokenStorage;
    private SerializerInterface $serializer;
    private $scenarioRepository;
    private $uploadService;

    public function __construct(UploadService $uploadService,ScenarioRepository $scenarioRepository,SerializerInterface $serializer,TokenStorageInterface $tokenStorage,ParamsHelper $paramsHelper,ResponseService $responseService,TestRepository $testRepository, EntityManagerInterface $entityManager, UploadVideo $uploadVideo,MessageBusInterface $messageBus,VideoAnalyze $videoAnalyze)
    {
        $this->testRepository = $testRepository;
        $this->entityManager = $entityManager;
        $this->uploadVideo = $uploadVideo;
        $this->messageBus = $messageBus;
        $this->videoAnalyze = $videoAnalyze;
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->scenarioRepository = $scenarioRepository;
        $this->uploadService = $uploadService;
    }

    public function submitAnswersAndAnalyze()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tester = $this->getCurrentUser()->getUser();
            $test = $this->testRepository->findOneBy(['id' => $inputs["idtest"], "clientTester" => $tester, "scenario" => $inputs["idscenario"]]);
            if (!$test)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            if(isset($test) and $test->getEtat() == 0)
            {
                $dataAnswers = $this->serializer->deserialize($inputs["answers"], AnswersDto::class, 'json');
                $answer = $this->serializer->deserialize(json_encode($dataAnswers->getAnswers(),true), Answer::class,'json');
                $step = $this->entityManager->getRepository(Step::class)->findOneBy(["id" => $inputs["step_id"]]);
                    if (!$step)
                    {
                        return $this->responseService->getResponseToClient(null, 404, 'steps.not_found');
                    }
                    if ($this->entityManager->getRepository(Answer::class)->findOneBy(["step" => $step,"test" => $test, "clientTester" => $tester]))
                    {
                        return $this->responseService->getResponseToClient(null, 201, 'responses.already_exist');
                    }
//                    $paths = [];
//                    foreach ($inputs["faceshots"] as $faceshot){
//                        $faceshots = new FaceShot();
//                        $path = $this->uploadService->uploadTestFaceShotsImage($faceshot['image']);
//                        $paths[] = $path;
//                        $faceshots->setImage($path);
//                        $faceshots->setFaceshotNumber($faceshot['number']);
//                        $faceshots->setFaceshotTimestamp(new \DateTime($faceshot['date']));
//                        $faceshots->setAnswer($answer);
//                        $this->entityManager->persist($faceshots);
//                    }
//                    $faceshots->setImages($paths);
                    $answer->setTest($test);
                    $answer->setClientTester($tester);
                    $step->addAnswer($answer);
                    //$this->entityManager->persist($faceshots);
                    $this->entityManager->persist($answer);
                    $this->entityManager->flush();

                if ($inputs['ended'] == 1)
                {
                    $test->setVideo(null);
                    $test->setEtat(2);
                    $test->setIsInterrupted(false);
                    $dispatch = $this->messageBus->dispatch(new VideoAnalyzeMessage($test->getId()));
                    $this->responseService->getResponseToClient(['Dispatch video' => $dispatch]);
                    $this->entityManager->persist($test);
                    $this->entityManager->flush();
                }else
                {
                    return $this->responseService->getResponseToClient(['test_id' => $test->getId(),'answer_id' => $answer->getId()]);
                }


            } else {
                return $this->responseService->getResponseToClient(null, 201, 'test.already_sumitted');
            }

            return $this->responseService->getResponseToClient(['test_id' => $test->getId(),'answer_id' => $answer->getId()],200,"test.submitted");

        }catch(\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }
}
<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Scenario;
use App\Entity\Step;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class FaceRecognitionService
{
    private EntityManagerInterface $entityManager;
    private ScenarioRepository $scenarioRepository;
    private SerializerInterface $serializer;
    private TestRepository $testRepository;
    private ResponseService $responseService;
    private TokenStorageInterface $tokenStorage;
    private ParamsHelper $paramsHelper;
    private DataByStep $dataByStep;

    public function __construct(DataByStep $dataByStep,ParamsHelper $paramsHelper,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, ScenarioRepository $scenarioRepository, SerializerInterface $serializer,TestRepository $testRepository)
    {
        $this->entityManager = $entityManager;
        $this->scenarioRepository = $scenarioRepository;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->paramsHelper = $paramsHelper;
        $this->dataByStep = $dataByStep;
    }

    public function stepsEmotionsDetailsByScenario(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $testersNb = $scenario->getPanel()->getTestersNb();
            if ($testersNb == 0)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $response = [];
            foreach ($scenario->getSteps() as $step){
                $calm = [];
                $angry = [];
                $sad = [];
                $confused = [];
                $disgusted = [];
                $surprised = [];
                $happy = [];
                $fear = [];
                foreach($step->getAnswers() as $answer){
                    array_push($calm, $answer->getCalm());
                    array_push($angry,$answer->getAngry());
                    array_push($sad,$answer->getSad());
                    array_push($confused,$answer->getConfused());
                    array_push($disgusted,$answer->getDisgusted());
                    array_push($surprised,$answer->getSurprised());
                    array_push($happy,$answer->getHappy());
                    array_push($fear,$answer->getFear());
                }

                $calmAvg = $this->dataByStep->average($calm);
                $angryAvg = $this->dataByStep->average($angry);
                $sadAvg = $this->dataByStep->average($sad);
                $confusedAvg = $this->dataByStep->average($confused);
                $disgustedAvg = $this->dataByStep->average($disgusted);
                $surprisedAvg = $this->dataByStep->average($surprised);
                $happyAvg = $this->dataByStep->average($happy);
                $fearAvg = $this->dataByStep->average($fear);
                $step->setEmotionsAVG([
                    'calm' => $calmAvg,
                    'angry' => $angryAvg,
                    'sad' => $sadAvg,
                    'confused' => $confusedAvg,
                    'disgusted' => $disgustedAvg,
                    'surprised' => $surprisedAvg,
                    'happy' => $happyAvg,
                    'fear' => $fearAvg,
                ]);

                $calmDev = $this->dataByStep->deviation($calm, $calmAvg);
                $angryDev = $this->dataByStep->deviation($angry, $angryAvg);
                $sadDev = $this->dataByStep->deviation($sad, $sadAvg);
                $confusedDev = $this->dataByStep->deviation($confused, $confusedAvg);
                $disgustedDev = $this->dataByStep->deviation($disgusted, $disgustedAvg);
                $surprisedDev = $this->dataByStep->deviation($surprised, $surprisedAvg);
                $happyDev = $this->dataByStep->deviation($happy, $happyAvg);
                $fearDev = $this->dataByStep->deviation($fear, $fearAvg);
                $step->setEmotionsDeviation([
                    'calm' => $calmDev,
                    'angry' => $angryDev,
                    'sad' => $sadDev,
                    'confused' => $confusedDev,
                    'disgusted' => $disgustedDev,
                    'surprised' => $surprisedDev,
                    'happy' => $happyDev,
                    'fear' => $fearDev,
                ]);

                $labels = 'E'.$step->getNumber();
                $calm = array_filter($calm, 'strlen');
                sort($calm);
                $calmMin = empty($calm) ? -1 : reset($calm);
                $calmMax = empty($calm) ? -1 : end($calm);

                $angry = array_filter($angry, 'strlen');
                sort($angry);
                $angryMin = empty($angry) ? -1 : reset($angry);
                $angryMax = empty($angry) ? -1 : end($angry);

                $sad = array_filter($sad, 'strlen');
                sort($sad);
                $sadMin = empty($sad) ? -1 : reset($sad);
                $sadMax = empty($sad) ? -1 : end($sad);

                $confused = array_filter($confused, 'strlen');
                sort($confused);
                $confusedMin = empty($confused) ? -1 : reset($confused);
                $confusedMax = empty($confused) ? -1 : end($confused);

                $disgusted = array_filter($disgusted, 'strlen');
                sort($disgusted);
                $disgustedMin = empty($disgusted) ? -1 : reset($disgusted);
                $disgustedMax = empty($disgusted) ? -1 : end($disgusted);

                $surprised = array_filter($surprised, 'strlen');
                sort($surprised);
                $surprisedMin = empty($surprised) ? -1 : reset($surprised);
                $surprisedMax = empty($surprised) ? -1 : end($surprised);

                $happy = array_filter($happy, 'strlen');
                sort($happy);
                $happyMin = empty($happy) ? -1 : reset($happy);
                $happyMax = empty($happy) ? -1 : end($happy);

                $fear = array_filter($fear, 'strlen');
                sort($fear);
                $fearMin = empty($fear) ? -1 : reset($fear);
                $fearMax = empty($fear) ? -1 : end($fear);

                $response[] = [
                    "stepId" => $step->getId(),
                    "labels" => $labels,
                    "average" => [
                        'calm' => $this->truncateToTwoDecimals($calmAvg),
                        'angry' => $this->truncateToTwoDecimals($angryAvg),
                        'sad' => $this->truncateToTwoDecimals($sadAvg),
                        'confused' => $this->truncateToTwoDecimals($confusedAvg),
                        'disgusted' => $this->truncateToTwoDecimals($disgustedAvg),
                        'surprised' => $this->truncateToTwoDecimals($surprisedAvg),
                        'happy' => $this->truncateToTwoDecimals($happyAvg),
                        'fear' => $this->truncateToTwoDecimals($fearAvg),
                    ],
                    "deviation" => [
                        'calm' => $this->truncateToTwoDecimals($calmDev),
                        'angry' => $this->truncateToTwoDecimals($angryDev),
                        'sad' => $this->truncateToTwoDecimals($sadDev),
                        'confused' => $this->truncateToTwoDecimals($confusedDev),
                        'disgusted' => $this->truncateToTwoDecimals($disgustedDev),
                        'surprised' => $this->truncateToTwoDecimals($surprisedDev),
                        'happy' => $this->truncateToTwoDecimals($happyDev),
                        'fear' => $this->truncateToTwoDecimals($fearDev),
                    ],
                    "min" => [
                        'calm' => $this->truncateToTwoDecimals($calmMin),
                        'angry' => $this->truncateToTwoDecimals($angryMin),
                        'sad' => $this->truncateToTwoDecimals($sadMin),
                        'confused' => $this->truncateToTwoDecimals($confusedMin),
                        'disgusted' => $this->truncateToTwoDecimals($disgustedMin),
                        'surprised' => $this->truncateToTwoDecimals($surprisedMin),
                        'happy' => $this->truncateToTwoDecimals($happyMin),
                        'fear' => $this->truncateToTwoDecimals($fearMin),
                    ],
                    "max" => [
                        'calm' => $this->truncateToTwoDecimals($calmMax),
                        'angry' => $this->truncateToTwoDecimals($angryMax),
                        'sad' => $this->truncateToTwoDecimals($sadMax),
                        'confused' => $this->truncateToTwoDecimals($confusedMax),
                        'disgusted' => $this->truncateToTwoDecimals($disgustedMax),
                        'surprised' => $this->truncateToTwoDecimals($surprisedMax),
                        'happy' => $this->truncateToTwoDecimals($happyMax),
                        'fear' => $this->truncateToTwoDecimals($fearMax),
                    ],
                    "testersNb" => $testersNb
                ];

                $this->entityManager->persist($step);
                $this->entityManager->flush();
            }
            usort($response, function($x, $y) {
                return trim($x['labels'],'E') <=> trim($y['labels'],'E');
            });

            return $this->responseService->getResponseToClient($response);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getStepsFaceshotsEmotionsDetails(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $answer = $this->entityManager->getRepository(Answer::class)->findOneBy(["id" => $inputs["answer_id"], "clientTester" => $inputs["tester_id"]]);
            if (!$answer)
            {
                return $this->responseService->getResponseToClient(null, 404, 'answer.not_found');
            }
            $response = $this->serializer->serialize($answer, 'json', ['groups' => 'answer_face_shots_emotion_per_photo']);

            return $this->responseService->getResponseToClient(json_decode($response, true));

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getAnalyzByStepEmotions(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $step = $this->entityManager->getRepository(Step::class)->findOneBy(['id' => $inputs['step_id']]);
            if (!$step)
            {
                return $this->responseService->getResponseToClient(null, 404, 'steps.not_found');
            }
            $filtredanswer = array();
            foreach ($step->getAnswers() as $answer)
            {
                if ($answer->getTest()->getIsAnalyzed())
                {
                    array_push($filtredanswer,[
                        'answer_id' => $answer->getId(),
                        'answer' => $answer->getAnswer(),
                        'comment' => $answer->getComment(),
                        'happy' => $this->truncateToTwoDecimals($answer->getHappy()),
                        'calm' => $this->truncateToTwoDecimals($answer->getCalm()),
                        'angry' => $this->truncateToTwoDecimals($answer->getAngry()),
                        'sad' => $this->truncateToTwoDecimals($answer->getSad()),
                        'confused' => $this->truncateToTwoDecimals($answer->getConfused()),
                        'disgusted' => $this->truncateToTwoDecimals($answer->getDisgusted()),
                        'fear' => $this->truncateToTwoDecimals($answer->getFear()),
                        'surprised' => $this->truncateToTwoDecimals($answer->getSurprised()),
                        'duration' => $answer->getDuration(),
                        'comments' => $answer->getComments(),
                        'clientComment' => $answer->getClientComment(),
                        'tester_id' => $answer->getClientTester()->getId(),
                        'tester_name' => $answer->getClientTester()->getName(),
                        'tester_lastName' => $answer->getClientTester()->getLastname()
                    ]);
                }

            }
            usort($filtredanswer, function($a, $b) {
                return $a['tester_id'] - $b['tester_id'];
            });
            $duration = $this->stepDuration($step);
            $response = $this->serializer->serialize(
                [
                    'step_id' => $step->getId(),
                    'answers' => $filtredanswer,
                    'step_duration' => $this->truncateToTwoDecimals($duration),
                    'scoreAVG' => $this->truncateToTwoDecimals($step->getAverage()),
                    'deviationAVG' => $this->truncateToTwoDecimals($step->getDeviation()?$step->getDeviation():0),
                ],
                'json',
                ['groups' => 'google_step']
            );

            return $this->responseService->getResponseToClient(json_decode($response,true));
        }catch(\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }

    }

    public function stepDuration(Step $step){
        $duree=[];
        foreach($step->getAnswers() as $answer){
            array_push($duree,$answer->getDuration());
        }
        return  $this->dataByStep->average($duree);
    }

    private function truncateToTwoDecimals($number)
    {
        return substr($number, 0, strpos($number, '.') + 3);
    }
}
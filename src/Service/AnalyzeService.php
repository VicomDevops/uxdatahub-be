<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Entity\Step;
use App\Entity\Test;
use App\Repository\PanelRepository;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AnalyzeService
{
    private EntityManagerInterface $entityManager;
    private $scenarioRepository;
    private $serializer;
    private $testRepository;
    private ResponseService $responseService;
    private $tokenStorage;
    private $normalizer;
    private ParamsHelper $paramsHelper;
    private DataByStep $dataByStep;

    public function __construct(DataByStep $dataByStep,ParamsHelper $paramsHelper,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, ScenarioRepository $scenarioRepository, SerializerInterface $serializer,TestRepository $testRepository)
    {
        $this->entityManager = $entityManager;
        $this->scenarioRepository = $scenarioRepository;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->paramsHelper = $paramsHelper;
        $this->dataByStep = $dataByStep;
    }

    public function getAnalyzeStepsByScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $testersNb = count($scenario->getTests());
            if ($testersNb == 0)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $response = [];
            foreach ($scenario->getSteps() as $step){
                $scores = [];
                $commentscores = array();
                foreach($step->getAnswers() as $answer){
                    array_push($scores,$answer->getScore());
                }
                foreach($step->getAnswers() as $answer){
                    array_push($commentscores,$answer->getScoreComments());
                }
                $average = $this->dataByStep->average($scores);
                $step->setAverage($average);
                $deviation = $this->dataByStep->deviation($scores,$average);
                $step->setDeviation($deviation);

                $averageComment = $this->dataByStep->average($commentscores);
                $step->setAverageComments($averageComment);
                $deviationComment = $this->dataByStep->deviation($commentscores,$averageComment);
                $step->setDeviationComments($deviationComment);

                $labels = 'E'.$step->getNumber();
                sort($scores);
                $scores = array_filter($scores, 'strlen');
                $response [] = [
                    "stepId" => $step->getId(),
                    "labels" => $labels,
                    "average" => $this->truncateToTwoDecimals($average),
                    "deviation" => $this->truncateToTwoDecimals($deviation),
                    "min" => empty($scores) ? "" : reset($scores),
                    "max" => empty($scores) ? "" : end($scores),
                    "testersNb" => $testersNb
                ];
            }
            usort($response, function($x, $y) {
                return trim($x['labels'],'E') <=> trim($y['labels'],'E');
            });
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient($response);
        }catch (\Exception $exception)
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

    public function getAverageScoreDuration($tester)
    {
        $Testers = json_decode($tester, true);
        $newFields = [
            'AvgDuration' => 0,
            'AvgScore' => 0
        ];
        $TestersAnsewrs = array_merge($Testers,$newFields);
        try {
            $answersnumber = count($TestersAnsewrs['answers']);
            $sumDuration = array_sum(array_column($TestersAnsewrs['answers'],'duration'));
            $sumScore = array_sum(array_column($TestersAnsewrs['answers'],'score'));
            $AvgDuration = $sumDuration / $answersnumber;
            $AvgScore = $sumScore / $answersnumber;
            $TestersAnsewrs["AvgDuration"] = $this->truncateToTwoDecimals($AvgDuration);
            $TestersAnsewrs["AvgScore"] = $this->truncateToTwoDecimals($AvgScore);

            return json_encode($TestersAnsewrs);

        }catch (\Exception $exception)
        {
            return json_encode($TestersAnsewrs);
        }
    }
    public function analyzedAnswersByTest()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $test = $this->entityManager->getRepository(Test::class)->findOneBy(['id' => $inputs['idtest']]);
            if (!$test)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $response = $this->serializer->serialize($test->getAnswers(), 'json', ['groups' => 'analyze_by_step_and_tester']);

            return $this->responseService->getResponseToClient($response);
        }catch(\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }

    }

    public function getAnalyzByStep()
    {
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
                        'videoText' => $answer->getVideoText(),
                        'magnitude' => $this->truncateToTwoDecimals($answer->getMagnitude()),
                        'score' => $this->truncateToTwoDecimals($answer->getScore()),
                        'sentences' => $answer->getSentences(),
                        'saliences' => $answer->getSaliences(),
                        'duration' => $answer->getDuration(),
                        'comments' => $answer->getComments(),
                        'clientComment' => $answer->getClientComment(),
                        'scoreVideo' => $answer->getScoreVideo(),
                        'magnitudeVideo' => $answer->getMagnitudeVideo(),
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
                    'score' => $this->truncateToTwoDecimals($step->getAverage()),
                    'deviation' => $this->truncateToTwoDecimals($step->getDeviation()?$step->getDeviation():0),
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

    public function getAnalyzByStepWithComments()
    {
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
                        'videoText' => $answer->getVideoText(),
                        'magnitude' => $this->truncateToTwoDecimals($answer->getMagnitudeComments()),
                        'score' => $this->truncateToTwoDecimals($answer->getScoreComments()),
                        'sentences' => $answer->getSentences(),
                        'saliences' => $answer->getSaliences(),
                        'duration' => $answer->getDuration(),
                        'comments' => $answer->getComments(),
                        'clientComment' => $answer->getClientComment(),
                        'scoreVideo' => $answer->getScoreVideo(),
                        'magnitudeVideo' => $answer->getMagnitudeVideo(),
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
                    'score' => $this->truncateToTwoDecimals($step->getAverageComments()),
                    'deviation' => $this->truncateToTwoDecimals($step->getDeviationComments()?$step->getDeviationComments():0),
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

    public function getJourneyMapByTestsList()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(['id' => $inputs['scenario_id']]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $response = $this->journeyMapByTests($scenario);

            return $this->responseService->getResponseToClient($response);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }

    }

    public function journeyMapByTests(Scenario $scenario)
    {
        $labels = [];
        $data=[];
        foreach ($scenario->getTests() as $test)
        {
            if ($test->getIsAnalyzed())
            {
                $scores = $this->collectScores($test);
                $average = $this->dataByStep->average($scores);
                $deviation = $this->dataByStep->deviation($scores,$average);
                $test->setAverage($average);
                if($test->getTester() == null)
                {
                    $labels = 'T' . substr($test->getClientTester()->getName(), 0, 1).substr($test->getClientTester()->getLastname(), 0, 1);
                }else{
                    $labels = 'T' . substr($test->getTester()->getName(), 0, 1).substr($test->getTester()->getLastname(), 0, 1);

                }
                $this->entityManager->flush();
                sort($scores);
                $scores = array_filter($scores, 'strlen');
                $data[] = [
                    "average" => $this->truncateToTwoDecimals($average),
                    "deviation" => $this->truncateToTwoDecimals($deviation),
                    "labels" => $labels,
                    "min" => empty($scores) ? 0 : reset($scores),
                    "max" => empty($scores) ? 0 : end($scores),
                    "testerId" => $test->getClientTester()->getId()
                ];
            }
        }
        return $data;
    }

    public function getAnalyzeWithCommentsStepsByScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $testersNb = count($scenario->getTests());
            if ($testersNb == 0)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $response = [];
            foreach ($scenario->getSteps() as $step){
                $scores = [];
                foreach($step->getAnswers() as $answer){
                    array_push($scores,$answer->getScoreComments());
                }
                $average = $this->dataByStep->average($scores);
                $step->setAverage($average);
                $deviation = $this->dataByStep->deviation($scores,$average);
                $step->setDeviation($deviation);
                $labels = 'E'.$step->getNumber();
                sort($scores);
                $scores = array_filter($scores, 'strlen');
                $response [] = [
                    "stepId" => $step->getId(),
                    "labels" => $labels,
                    "average" => $this->truncateToTwoDecimals($average),
                    "deviation" => $this->truncateToTwoDecimals($deviation),
                    "min" => empty($scores) ? "" : reset($scores),
                    "max" => empty($scores) ? "" : end($scores),
                    "testersNb" => $testersNb
                ];
            }
            usort($response, function($x, $y) {
                return trim($x['labels'],'E') <=> trim($y['labels'],'E');
            });
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient($response);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    private function collectScores($test): array
    {
        $scores=[];
        foreach($test->getAnswers() as $answer){
            array_push($scores,$answer->getScore());
        }
        return $scores;
    }

    private function truncateToTwoDecimals($number)
    {
        return substr($number, 0, strpos($number, '.') + 3);
    }
}
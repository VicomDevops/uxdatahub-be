<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\ClientTester;
use App\Entity\Scenario;
use App\Entity\Step;
use App\Entity\Tester;
use App\Repository\AnswerRepository;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class StatisticsService
{
    private $entityManager;
    private $scenarioRepository;
    private $serializer;
    private $testRepository;
    private $responseService;
    private $tokenStorage;
    private $normalizer;
    private $paramsHelper;
    private $userRepository;
    private $answerRepository;

    public function __construct(AnswerRepository $answerRepository,UserRepository $userRepository,ParamsHelper $paramsHelper ,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, ScenarioRepository $scenarioRepository, SerializerInterface $serializer,TestRepository $testRepository)
    {
        $this->entityManager = $entityManager;
        $this->scenarioRepository = $scenarioRepository;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->paramsHelper = $paramsHelper;
        $this->userRepository = $userRepository;
        $this->answerRepository = $answerRepository;
    }

    public function getDataByTester()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $user = $this->userRepository->findOneBy(['id' => $inputs["tester_id"]]);
            if (!$user)
            {
                return $this->responseService->getResponseToClient(null, 404, 'users.not_found');
            }
            if ($user instanceof Tester) {
                $test = $this->testRepository->findBy(['tester' => $user, "scenario" => $inputs["scenario_id"]]);
            } else if ($user instanceof ClientTester) {
                $test = $this->testRepository->findBy(['clientTester' => $user, "scenario" => $inputs["scenario_id"]]);
            }else
            {
                return $this->responseService->getResponseToClient(null, 500, 'general.exception');
            }
            $response = $this->serializer->serialize($test, 'json', ['groups' => 'get_test']);
            $response = json_decode($response, true);
            if ($response && isset($response[0]['answers']))
            {
                usort($response[0]['answers'], function ($a, $b) {
                    return $a['step']['id'] - $b['step']['id'];
                });
            }else
            {
                return $this->responseService->getResponseToClient($response);
            }

            return $this->responseService->getResponseToClient($response);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getDataByStep()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $step = $this->entityManager->getRepository(Step::class)->findOneBy(["id" => $inputs["id"]]);
            if (!$step)
            {
                return $this->responseService->getResponseToClient(null, 404, 'steps.not_found');
            }
            $result = [];
            $result["question"] = $step->getQuestion();
            $result["number"] = $step->getNumber();
            $result["answers"] = json_decode($this->serializer->serialize($step->getAnswers(), 'json', ['groups' => 'data_by_scenario']), true);
            $result["type"] = $step->getType();
            if ($step->getType() == "scale")
            {
                $result["MinScale"] = 1;
                $result["MaxScale"] = $step->getQuestionChoices()->getMaxScale();
                $result["InfBorne"] = $step->getQuestionChoices()->getBorneInf();
                $result["SupBorne"] = $step->getQuestionChoices()->getBorneSup();
            }
            $result["totalResponse"] = count($step->getAnswers());
            if ($step->getType() == "scale" || $step->getType() == "close")
            {
                $result["Graph"] = $this->Graph($step);
            }

            return $this->responseService->getResponseToClient($result);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getDataByScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $steps = $this->entityManager->getRepository(Step::class)->findBy(
                ["scenario" => $inputs["scenario_id"]],
                ["number" => "ASC"]
            );
            if (!$steps)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $result = [];
            foreach ($steps as $step)
            {
                $result[$step->getNumber()]['step_number'] = $step->getNumber();
                $result[$step->getNumber()]['question'] = $step->getQuestion();
                $result[$step->getNumber()]['type'] = $step->getType();
                $answers = json_decode($this->serializer->serialize($step->getAnswers(), 'json', ['groups' => 'data_by_scenario']), true);
                $filteredAnswers = array_filter($answers, function ($answer) {
                    return $answer['test']['isAnalyzed'] === true;
                });
                usort($filteredAnswers, function ($a, $b) {
                    return $a['clientTester']['id'] - $b['clientTester']['id'];
                });
                $result[$step->getNumber()]['answers'] = $filteredAnswers;
                if ($step->getType() == "scale")
                {
                    $result[$step->getNumber()]["MinScale"] = 1;
                    $result[$step->getNumber()]["MaxScale"] = $step->getQuestionChoices()->getMaxScale();
                    $result[$step->getNumber()]["InfBorne"] = $step->getQuestionChoices()->getBorneInf();
                    $result[$step->getNumber()]["SupBorne"] = $step->getQuestionChoices()->getBorneSup();
                }
                $result[$step->getNumber()]["totalResponse"] = count($filteredAnswers);
                if ($step->getType() == "scale" || $step->getType() == "close")
                {
                    $result[$step->getNumber()]["Graph"] = $this->Graph($step);
                }
            }


            return $this->responseService->getResponseToClient($result);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }


    public function getVideoByAnswerAndTester()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $answer = $this->entityManager->getRepository(Answer::class)->findOneBy(["id" => $inputs["answer_id"],"clientTester" => $inputs["tester_id"]]);
            if (!$answer)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }

            $response = $this->serializer->serialize($answer, 'json', ['groups' => 'video_answer_tester']);
            $resp = json_decode($response,true);
            $resp['video'] = isset($resp['video']);

            return $this->responseService->getResponseToClient($resp);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    private function Graph($step):array
    {
        $graph = [];
        $answers = [];
        foreach ($step->getAnswers() as $answer)
        {
            $answers[] = $answer->getAnswer();
        }
        if ($step->getType() == "scale")
        {
            for ($i=1; $i<=$step->getQuestionChoices()->getMaxScale();$i++)
            {
                $graph[$i] = array_count_values($answers)[$i]??0;
            }
        }else
        {
            for($i=1; $i<=6; $i++)
            {
                $func = "getChoice$i";
                if ($step->getQuestionChoices()->$func() != null)
                {
                    $graph[$step->getQuestionChoices()->$func()] = array_count_values($answers)[$step->getQuestionChoices()->$func()]??0;
                }
            }
        }
        return $graph;
    }

}
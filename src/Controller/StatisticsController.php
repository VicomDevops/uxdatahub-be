<?php

namespace App\Controller;


use App\Service\ResponseService;
use App\Service\StatisticsService;
use App\Utils\ParamsHelper;
use App\Validator\Scenarios\StatisticsTesterValidator;
use App\Validator\Scenarios\StatisticsByScenarioValidator;
use App\Validator\Scenarios\VideoAnswerTesterValidator;
use App\Validator\Steps\StatisticsByStepValidator;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/stats")
 * @OA\Tag(name="Stats")
 */
class StatisticsController extends AbstractController
{
    /**
     * @Route("/step", name="api_data_by_step", methods={"GET"})
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="id step",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function dataByStep(Request $request,ParamsHelper $paramsHelper, LoggerInterface $statisticsLogger, ValidatorInterface $validator, ResponseService $responseService,StatisticsService $statisticsService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($statisticsLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsByStepValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $statisticsService->getDataByStep();
    }

    /**
     * @Route("/scenario", name="api_data_by_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="id scenario",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function allDataByScenario(Request $request,ParamsHelper $paramsHelper, LoggerInterface $statisticsLogger, ValidatorInterface $validator, ResponseService $responseService,StatisticsService $statisticsService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($statisticsLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $statisticsService->getDataByScenario();
    }

    /**
     * @Route("/tester/scenario", name="api_data_by_tester", methods={"GET"})
     * @OA\Parameter(
     *     name="tester_id",
     *     in="query",
     *     description="id tester",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="id scenario",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function dataByTester(Request $request,ParamsHelper $paramsHelper, LoggerInterface $statisticsLogger, ValidatorInterface $validator, ResponseService $responseService,StatisticsService $statisticsService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($statisticsLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $statisticsService->getDataByTester();
    }

    /**
     * @Route("/tester/step/video", name="api_video_answer_step_tester", methods={"GET"})
     * @OA\Parameter(
     *     name="tester_id",
     *     in="query",
     *     description="id tester",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="answer_id",
     *     in="query",
     *     description="id answer",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function videoByAnswerAndTester(Request $request,ParamsHelper $paramsHelper, LoggerInterface $statisticsLogger, ValidatorInterface $validator, ResponseService $responseService,StatisticsService $statisticsService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($statisticsLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new VideoAnswerTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $statisticsService->getVideoByAnswerAndTester();
    }

}

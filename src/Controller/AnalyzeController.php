<?php

namespace App\Controller;

use App\Entity\Test;
use App\Service\AnalyzeService;
use App\Service\GoogleApi;
use App\Service\ResponseService;
use App\Service\VideoAnalyze;
use App\Service\VideoFfmpeg;
use App\Utils\ParamsHelper;
use App\Validator\Tests\analyzedAnswersByTestValidator;
use App\Validator\Scenarios\analyzedStepsPerScenarioValidator;
use App\Validator\Scenarios\StatisticsByScenarioValidator;
use App\Validator\Steps\AnalyzeByStepValidator;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\JWT\Token\DataSet;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/analyze")
 * @OA\Tag(name="Analyses")
 */
class AnalyzeController extends AbstractController
{
    /**
     * @Route("/scenario", name="api_get_analyzed_by_steps_per_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="ID of the scenario",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function analyzedStepsPerScenario(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, ResponseService $responseService,AnalyzeService $analyzeService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new analyzedStepsPerScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $analyzeService->getAnalyzeStepsByScenario();
    }

    /**
     * @Route("/step", name="api_get_analyzed_data_by_step", methods={"GET"})
     * @OA\Parameter(
     *     name="step_id",
     *     in="query",
     *     description="ID of the step",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function analyzeByStep(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, ResponseService $responseService,AnalyzeService $analyzeService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new AnalyzeByStepValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $analyzeService->getAnalyzByStep();
    }

    /**
     * @Route("/comments/step", name="api_get_analyzed_comments_data_by_step", methods={"GET"})
     * @OA\Parameter(
     *     name="step_id",
     *     in="query",
     *     description="ID of the step",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function analyzeByStepWithComments(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, ResponseService $responseService,AnalyzeService $analyzeService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new AnalyzeByStepValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $analyzeService->getAnalyzByStepWithComments();
    }

    /**
     * @Route("/average/tests/scenario", name="api_journey_map_tests", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="ID of the scenario",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */

    public function getJourneyMapByTests(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, ResponseService $responseService,AnalyzeService $analyzeService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $analyzeService->getJourneyMapByTestsList();
    }

    /**
     * @Route("/test", name="api_get_analyze_by_test", methods={"GET"})
     * @OA\Parameter(
     *    name="idtest",
     *    in="query",
     *    description="ID de test",
     *    required=true,
     *    @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the answers of a test",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*",
     *     )
     * )
     */

    public function getAnalyzedAnswersByTest(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, AnalyzeService $analyzeService, ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new analyzedAnswersByTestValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $analyzeService->analyzedAnswersByTest();
    }


    /**
     * @Route("/comments/scenario", name="api_get_analyze_with_comments_by_steps_per_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="ID of the scenario",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function analyzWithCommentsStepsPerScenario(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, ResponseService $responseService,AnalyzeService $analyzeService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new analyzedStepsPerScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $analyzeService->getAnalyzeWithCommentsStepsByScenario();
    }
}

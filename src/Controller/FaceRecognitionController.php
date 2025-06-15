<?php

namespace App\Controller;

use App\Entity\Scenario;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Service\FaceRecognitionService;
use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use App\Validator\FaceRecognition\AnalyzeByStepEmotionsValidator;
use App\Validator\FaceRecognition\StepfaceRecogntionFaceshotsValidator;
use App\Validator\Scenarios\StepsDetailsByScenarioValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/facerecognition")
 * @OA\Tag(name="FaceRecognition")
 */
class FaceRecognitionController extends AbstractController
{

    /**
     * @Route("/scenario/details/emotions", name="api_face_recognition_by_scenario", methods={"GET"})
     * @OA\Parameter(
     *      name="scenario_id",
     *      in="query",
     *      description="ID scenario",
     *      required=true,
     *      @OA\Schema(type="string")
     *  )
     * @OA\Response(
     *      response=200,
     *      description="Success response 200",
     *      @OA\JsonContent(
     *          type="object",
     *          example="*"
     *      )
     *  )
     */
    public function faceRecogntionByScenario(ValidatorInterface $validator,FaceRecognitionService $faceRecognitionService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StepsDetailsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $faceRecognitionService->stepsEmotionsDetailsByScenario();
    }

    /**
     * @Route("/step/details/emotions", name="api_face_recognition_by_step", methods={"GET"})
     * @OA\Parameter(
     *      name="step_id",
     *      in="query",
     *      description="ID step",
     *      required=true,
     *      @OA\Schema(type="string")
     *  )
     * @OA\Response(
     *      response=200,
     *      description="Success response 200",
     *      @OA\JsonContent(
     *          type="object",
     *          example="*"
     *      )
     *  )
     */
    public function faceRecogntionFaceshotsDetails(ValidatorInterface $validator,FaceRecognitionService $faceRecognitionService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StepfaceRecogntionFaceshotsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $faceRecognitionService->getStepsFaceshotsEmotionsDetails();
    }

    /**
     * @Route("/step/emotions", name="api_get_analyzed_data_by_step_emotions", methods={"GET"})
     * @OA\Parameter(
     *     name="step_id",
     *     in="query",
     *     description="ID of the step",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation and emotions of steps for a given scenario",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function analyzeByStep(Request $request,ParamsHelper $paramsHelper, LoggerInterface $googleAnalyzeLogger, ValidatorInterface $validator, ResponseService $responseService,FaceRecognitionService $faceRecognitionService,)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new AnalyzeByStepEmotionsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $faceRecognitionService->getAnalyzByStepEmotions();
    }
}

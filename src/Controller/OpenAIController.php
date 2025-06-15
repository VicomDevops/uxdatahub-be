<?php

namespace App\Controller;

use App\Service\OpenAIService;
use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use App\Validator\admin\AuditUXFlashValidator;
use App\Validator\Client\targetedRecommendationsByStepValidator;
use App\Validator\OpenAI\concreteRecommendationsByStepValidator;
use App\Validator\OpenAI\OpenAIAnalysesChatValidator;
use App\Validator\OpenAI\OpenAIAnalysesChatWithFilesValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("/api/openai")
 * @OA\Tag(name="OpenAI")
 */
class OpenAIController extends AbstractController
{
    /**
     * @Route("/open/a/i", name="api_app_open_a_i", methods={"POST"})
     * @OA\RequestBody(
     *     required=true,
     *     description="Message to analyse",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(
     *             property="messages",
     *             type="string",
     *             description="Message to analyse",
     *             example="Your message here"
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns response from openAI",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */
    public function sendMessagesToOpenAI(ValidatorInterface $validator,OpenAIService $openAIService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new OpenAIAnalysesChatValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $openAIService->getAnalysesFromOpenAI();
    }


    /**
     * @Route("/open/a/i/files", name="api_app_open_a_i_files", methods={"POST"})
     * @OA\RequestBody(
     *     required=true,
     *     description="Message and file to analyse",
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="file",
     *                 type="string",
     *                 format="binary",
     *                 description="File to upload"
     *             ),
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 format="url",
     *                 description="URL to analyze"
     *             ),
     *             @OA\Property(
     *                 property="workField",
     *                 type="string",
     *                 description="Work field related to the analysis"
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns response from OpenAI",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */
    public function sendMessagesAndFilesToOpenAI(ValidatorInterface $validator,OpenAIService $openAIService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = array_merge($request->files->all(), $request->request->all());
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new OpenAIAnalysesChatWithFilesValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $openAIService->getAnalysesFileFromOpenAI();
    }

    /**
     * @Route("/open/a/i/auditux/flash", name="api_app_open_a_i_audit_UX_flash", methods={"POST"})
     * @OA\RequestBody(
     *     required=true,
     *     description="Message to analyse",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(
     *             property="client",
     *             type="string",
     *             description="Client information",
     *             example="Client XYZ"
     *         ),
     *         @OA\Property(
     *              property="workField",
     *              type="string",
     *              description="Client workField information",
     *              example="XYZ"
     *          ),
     *         @OA\Property(
     *             property="scenarioName",
     *             type="string",
     *             description="Scenario name for the analysis",
     *             example="Scenario ABC"
     *         ),
     *         @OA\Property(
     *             property="url",
     *             type="string",
     *             description="URL for the analysis",
     *             example="https://example.com"
     *         ),
     *         @OA\Property(
     *             property="competingUrl1",
     *             type="string",
     *             description="First competing URL for comparison",
     *             example="https://competing1.com"
     *         ),
     *              @OA\Property(
     *              property="competingName1",
     *              type="string",
     *              description="First competing Name for comparison",
     *              example="competing Name 1"
     *          ),
     *         @OA\Property(
     *             property="competingUrl2",
     *             type="string",
     *             description="Second competing URL for comparison",
     *             example="https://competing2.com"
     *         ),
     *              @OA\Property(
     *              property="competingName2",
     *              type="string",
     *              description="Second competing Name for comparison",
     *              example="competing Name 2"
     *          ),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns response from openAI",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function generateAuditUXFlash(ValidatorInterface $validator,OpenAIService $openAIService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new AuditUXFlashValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $openAIService->getAuditUXFlash();
    }


    /**
     * @Route("/open/a/i/targeted/recommendations/step", name="app_open_a_i_targeted_recommendations_by_stage", methods={"GET"})
     * @OA\RequestBody(
     *     required=true,
     *     description="Scenario ID for analysis",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="scenario_id",
     *                 type="string",
     *                 description="Unique identifier for the scenario to analyze"
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns response from OpenAI",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */
    public function targetedRecommendationsByStepFromOpenAI(ValidatorInterface $validator,OpenAIService $openAIService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new targetedRecommendationsByStepValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $openAIService->getTargetedRecommendationsByStepFromOpenAI();
    }

    /**
     * @Route("/open/a/i/concrete/recommendations/steps", name="app_open_a_i_concrete_recommendations_by_stage", methods={"POST"})
     * @OA\RequestBody(
     *     required=true,
     *     description="Upload an XLSX file containing scenario data for analysis",
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="file",
     *                 type="string",
     *                 format="binary",
     *                 description="The XLSX file to upload"
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns response from OpenAI",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function concreteRecommendationsByStepFromOpenAI(ValidatorInterface $validator,OpenAIService $openAIService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $faceRecognitionLogger,ResponseService $responseService)
    {
        $inputs = array_merge($request->files->all(), $request->request->all());
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($faceRecognitionLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new concreteRecommendationsByStepValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $openAIService->getConcreteRecommendationsByStepFromOpenAI();
    }
}

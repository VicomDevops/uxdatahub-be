<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Scenario;
use App\Entity\Test;
use App\Repository\TestRepository;
use App\Service\ResponseService;
use App\Service\TestsServices;
use App\Service\VideoAnalyze;
use App\Service\VideoService;
use App\Utils\ParamsHelper;
use App\Validator\Tests\StartTestValidator;
use App\Validator\Tests\UploadVideoAnswersValidator;
use App\Validator\Tests\submitAnswersVideoToAnalyzeValidator;
use App\Validator\Tests\ResetTestWhenTesterInterruptValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/api/tests")
 * @OA\Tag(name="Tests")
 */
class TestController extends AbstractController
{
    /**
     * @Route("/scenario/{id}", name="get_tests_by_state", methods={"GET"})
     */
    public function getTests(Request $request, Scenario $scenario, TestRepository $testRepository, SerializerInterface $serializer)
    {
        $state = $request->query->get('state') ?? '';
        $user = $this->getUser();

        if ($this->getUser() instanceof Client) {
            $tests = $scenario->getTests();
        } else {
            $tests = $testRepository->findTestsByState($user, $state);
        }

        $json = $serializer->serialize($tests, 'json', ['groups' => 'get_test']);
        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="get_test", methods={"GET"})
     */
    public function getTest(Test $test, SerializerInterface $serializer)
    {
        $json = $serializer->serialize($test, 'json', ['groups' => 'get_test']);

        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/submit/test/reponses", name="api_video_submit_answers", methods={"POST"})
     *
     * @OA\RequestBody(
     *     required=true,
     *     description="Request body for submitting test responses",
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(property="idtest", type="string"),
     *             @OA\Property(property="idscenario", type="string"),
     *             @OA\Property(property="ended", type="string"),
     *             @OA\Property(property="answers", type="string"),
     *             @OA\Property(property="step_id", type="string"),
     *             @OA\Property(
     *                 property="faceshots",
     *                 type="array",
     *                 @OA\Items(type="string", format="binary")
     *             )
     *         )
     *     )
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function submitTesterAnswers(Request $request,ParamsHelper $paramsHelper, LoggerInterface $testLogger, ValidatorInterface $validator,VideoService $videoService, ResponseService $responseService)
    {
        $inputs = array_merge($request->request->all(),$request->files->all());
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($testLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new submitAnswersVideoToAnalyzeValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $videoService->submitAnswersAndAnalyze();
    }
    /**
     * @Route("/startTest/scenario", name="api_start_test", methods={"GET"})
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="Id scenario",
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
    public function startTest(Request $request,ParamsHelper $paramsHelper, LoggerInterface $testLogger, ValidatorInterface $validator, TestsServices $testsServices, ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($testLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StartTestValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $testsServices->setTestToStartStatus();
    }

    /**
     * @Route("/upload/video/command", name="api_start_upload", methods={"POST"})
     * @OA\RequestBody(
     *      required=true,
     *      description="Request body for uploading file",
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *               @OA\Property(property="test_id", type="string"),
     *               @OA\Property(property="answer_id", type="string"),
     *               @OA\Property(property="duration", type="string"),
     *               @OA\Property(
     *                  property="file",
     *                  description="Video file",
     *                  type="string",
     *                  format="binary"
     *              ),
     *          )
     *      )
     *  )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*"
     *     )
     * )
     */
    public function uploadVideo(Request $request,ParamsHelper $paramsHelper, LoggerInterface $testLogger, ValidatorInterface $validator, TestsServices $testsServices, ResponseService $responseService)
    {
        $inputs = array_merge($request->files->all(),$request->request->all());
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($testLogger);
        //$paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new UploadVideoAnswersValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $testsServices->setUploadFile();
    }

    /**
     * @Route("/tester/test/interrupt", name="api_set_interrupt_true_When_Tester_Interrupt", methods={"POST"})
     * @OA\RequestBody(
     *     request="set test as interrupted",
     *     required=true,
     *     description="JSON payload for test",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="test_id", type="string", example="1"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */
    public function setTestWhenTesterInterrupt(Request $request,ParamsHelper $paramsHelper, LoggerInterface $testLogger, ValidatorInterface $validator, TestsServices $testsServices, ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($testLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ResetTestWhenTesterInterruptValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $testsServices->setInterruptedTest();
    }

    /**
     * @Route("/test/{id}", name="api_test_4", methods={"POST"})
     */
    public function Testanalyze(Test $test, VideoAnalyze $videoAnalyze){
        return $videoAnalyze->analyze($test);
    }
}

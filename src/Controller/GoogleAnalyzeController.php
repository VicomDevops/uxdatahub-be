<?php

namespace App\Controller;

use App\Entity\Step;
use App\Service\GoogleService;
use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use App\Validator\Google\GoogleAnalyzePerTesterAndScenarioValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class GoogleAnalyzeController
 * @OA\Tag(name="Analyses")
 * @Route("/api/google-analyze")
 */
class GoogleAnalyzeController extends AbstractController
{
    /**
     * @Route("/tester/scenario", name="api_google_analyze_tester", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="id scenario",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="tester_id",
     *     in="query",
     *     description="id tester",
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
    public function getGoogleAnalyzePerTesterAndScenario(ValidatorInterface $validator,GoogleService $googleService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $googleAnalyzeLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($googleAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new GoogleAnalyzePerTesterAndScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $googleService->googleAnalyzePerTesterAndScenarioList();
    }

    /**
     * @Route("/step/{id}", methods={"GET"})
     */
    public function step(Step $step, SerializerInterface $serializer)
    {
        $json = $serializer->serialize($step, 'json', ['groups' => 'google_step']);
        return new Response($json, Response::HTTP_OK);
    }
}

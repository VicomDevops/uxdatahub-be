<?php

namespace App\Controller;

use App\Service\PanelServices;
use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use App\Validator\Panel\panelTestersStatisticsValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/analyze/panel")
 * @OA\Tag(name="Admin")
 */
class PanelAnalyzeController extends AbstractController
{
    /**
     * @Route("/testers", name="api_get_Panel_Gender_Analyze", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="ID of the scenario",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Data filter",
     *     required=true,
     *     @OA\Schema(type="string")
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
    public function getPanelTestersStatistics(Request $request,ParamsHelper $paramsHelper, LoggerInterface $panelAnalyzeLogger, PanelServices $panelServices,ValidatorInterface $validator, ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelAnalyzeLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new panelTestersStatisticsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->getPanelTestersStatisticsList();
    }
}
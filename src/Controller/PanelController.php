<?php

namespace App\Controller;


use App\Service\PanelServices;
use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use App\Validator\Panel\addClientTesterValidator;
use App\Validator\Panel\DeleteClientTesterValidator;
use App\Validator\Panel\DeletePanelValidator;
use App\Validator\Panel\DetailsPanelValidator;
use App\Validator\Panel\encloseScenariosValidator;
use App\Validator\Panel\PanelAssignScenarioValidator;
use App\Validator\Panel\RemoveTestsForTestersAfterFinishValidator;
use App\Validator\Panel\replaceClientTesterValidator;
use App\Validator\Panel\ResentScenarioNotificationsAndCredentialsValidator;
use App\Validator\Panel\unpassedClientTesterScenariosValidator;
use App\Validator\Panel\unsetClientTesterValidator;
use App\Validator\Panel\UpdateClientTesterValidator;
use App\Validator\Panel\UpdatePanelValidator;
use App\Validator\Panel\VerifyMailValidator;
use App\Validator\Scenarios\StatisticsByScenarioValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;


/**
 * @Route("/api/panels")
 * @OA\Tag(name="Panels")
 */
class PanelController extends AbstractController
{

    /**
     * @Route("/client/list", name="api_panels_client_list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*",
     *     )
     * )
     */

    public function getPanelsClient(PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();

        return $panelServices->panelsClient();
    }

    /**
     * @Route("/free/scenario/create/assign", name="api_create_add_panel", methods={"POST"})
     * @OA\RequestBody(
     *     description="Création panel de testeurs ou ajouter un existant",
     *     required=true
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *     type="string",
     *     example="*"
     * )
     * )
     */
    public function createAndAssignTesterAndPanelScenario(ValidatorInterface $validator,PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->createFreePanel();
    }

    /**
     * @Route("/free/scenario/update/assign", name="api_update_panel_scenario", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="panel_id", type="string"),
     *         @OA\Property(property="scenario_id", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*"
     *     )
     * )
     */

    public function panelAssignScenario(ValidatorInterface $validator,PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new PanelAssignScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->assignPanelToScenario();
    }

    /**
     * @Route("/free/update", name="api_update_panel", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="panel_id", type="string"),
     *         @OA\Property(property="panel", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*"
     *     )
     * )
     */

    public function updatePanel(ValidatorInterface $validator,PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new UpdatePanelValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->updatePanel();
    }

    /**
     * @Route("/free/delete", name="api_delete_panel", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="panel_id", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="0000"
     *     )
     * )
     */

    public function deletePanel(ValidatorInterface $validator,PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new DeletePanelValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->deletePanelById();
    }

    /**
     * @Route("/free/verify/mail/tester", name="api_verify_mail_before_add", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="email", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="example@labsoft.fr"
     *     )
     * )
     */

    public function verifyPanelMailsOnCreate(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new VerifyMailValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->getStatusEmailOnCreate();
    }

    /**
     * @Route("/free/client/tester/delete", name="api_delete_client_tester_from_panel", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="panel_id", type="string"),
     *         @OA\Property(property="client_tester_id", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="0000"
     *     )
     * )
     */
    public function removeClientTesterFromPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new DeleteClientTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->removeClientTesterFromPanelById();
    }


    /**
     * @Route("/free/client/tester/update", name="api_update_client_tester_from_panel", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for updating client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="client_tester_id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="ben hmed"),
     *         @OA\Property(property="lastname", type="string", example="Said"),
     *         @OA\Property(property="email", type="string", format="email", example="s.benhmed@labsoft.fr"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function updateClientTesterFromPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new UpdateClientTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->updateClientTesterFromPanelById();
    }

    /**
     * @Route("/free/tester/replace", name="api_replace_client_tester_in_panel", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for updating client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="current_client_tester_id", type="integer", example=1),
     *         @OA\Property(property="current_panel_id", type="integer", example=1),
     *         @OA\Property(property="new_email", type="string", example="example@example.com"),
     *         @OA\Property(property="new_name", type="string", example="name"),
     *         @OA\Property(property="new_lastname", type="string", example="lastname"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function replaceClientTesterInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new replaceClientTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->replaceClientTesterFromPanelById();
    }

    /**
     * @Route("/free/scenario/tester/detachement", name="api_Scenario_Detachement_client_tester_in_panel", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for scenario detachement by client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="scenario_id", type="integer", example=1),
     *         @OA\Property(property="new_email", type="string", example="example@example.com"),
     *         @OA\Property(property="new_name", type="string", example="name"),
     *         @OA\Property(property="new_lastname", type="string", example="lastname"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function clientTesterScenariosDetachmentInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new unsetClientTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->ClientTesterScenariosDetachment();
    }

    /**
     * @Route("/free/scenario/tester/unpassed", name="api_Scenario_notfinished_client_tester_in_panel_unpassed", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for unpassed scenarios client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="client_tester_id", type="integer", example=1),
     *         @OA\Property(property="panel_id", type="integer", example=1)
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the list of the scenarios over 5 notifications",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function unpassedClientTesterScenariosInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new unpassedClientTesterScenariosValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->getUnpassedClientTesterScenarios();
    }

    /**
     * @Route("/free/scenario/tester/passed", name="api_Scenario_notfinished_client_tester_in_panel", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for unpassed scenarios client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="client_tester_id", type="integer", example=1),
     *         @OA\Property(property="panel_id", type="integer", example=1)
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the list of the scenarios over 5 notifications",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function passedClientTesterScenariosInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new unpassedClientTesterScenariosValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->getpassedClientTesterScenarios();
    }

    /**
     * @Route("/free/scenario/tester/enclose", name="api_enclose_Scenario", methods={"POST"})
     * @OA\RequestBody(
     *     request="Panel Data",
     *     required=true,
     *     description="JSON payload for unpassed scenarios client tester",
     *     @OA\JsonContent(
     *         type="object",
     *              @OA\Property(property="scenario_id", type="integer", example=1),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns True if the enclose is OK",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function encloseScenariosInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new encloseScenariosValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->encloseAllScenariosInPanel();
    }

    /**
     * @Route("/free/scenario/tester/details", name="api_list_tester_details_in_panel", methods={"POST"})
     * @OA\RequestBody(
     *     request="Panel Data",
     *     required=true,
     *     description="JSON payload for details scenarios client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="panel_id", type="integer", example=1),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns Json data if OK",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function testersDetailsScenariosInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new DetailsPanelValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->getDetailsPanelById();
    }

    /**
     * @Route("/free/tester/add", name="api_add_client_tester_in_panel", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for updating client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="panel_id", type="integer", example=1),
     *         @OA\Property(property="email", type="string", example="example@example.com"),
     *         @OA\Property(property="name", type="string", example="name"),
     *         @OA\Property(property="lastname", type="string", example="lastname"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="*"
     *     )
     * )
     */

    public function addClientTesterInPanel(ValidatorInterface $validator,PanelServices $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new addClientTesterValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->addClientTesterToPanel();
    }

    /**
     * @Route("/free/tests/remove", name="api_tests_remove_scenario", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="tests_id", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*"
     *     )
     * )
     */

    public function removeTestsForTestersAfterFinish(ValidatorInterface $validator,PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new RemoveTestsForTestersAfterFinishValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->removeScenarioTesters();
    }

    /**
     * @Route("/free/resent/credentials", name="api_test_notification_scenario_crediantials", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="scenario_id", type="string"),
     *         @OA\Property(property="tester_id", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*"
     *     )
     * )
     */

    public function resentScenarioNotificationsAndCredentials(ValidatorInterface $validator,PanelServices  $panelServices, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ResentScenarioNotificationsAndCredentialsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $panelServices->getScenarioNotificationsAndCredentials();
    }

}

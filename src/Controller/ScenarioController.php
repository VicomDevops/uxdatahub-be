<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Scenario;
use App\Entity\Step;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Service\MathematicalFunctionsService;
use App\Service\ResponseService;
use App\Service\ScenarioService;
use App\Service\ValidationErrors;
use App\Utils\ParamsHelper;
use App\Validator\Scenarios\CreateScenarioValidator;
use App\Validator\Scenarios\DataFileXlsxValidator;
use App\Validator\Scenarios\ListQuestionsByScenarioValidator;
use App\Validator\Scenarios\PauseTestersScenariosValidator;
use App\Validator\Scenarios\ScenarioDetailsValidator;
use App\Validator\Scenarios\ScenarioNameValidator;
use App\Validator\Scenarios\StatisticsByScenarioValidator;
use App\Validator\Scenarios\StepsDetailsByScenarioValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/scenario")
 */
class ScenarioController extends AbstractController
{
    /**
     * @Route("/details", name="api_scenario_details", methods={"GET"})
     * @OA\Parameter(
     *     name="id",
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
    public function getScenariosInfos(Request $request,ParamsHelper $paramsHelper, LoggerInterface $scenarioLogger, ValidatorInterface $validator, ResponseService $responseService,ScenarioService $scenarioService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ScenarioDetailsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->getScenariosDetails();
    }

    /**
     * @Route("/testers", name="api_tester_details", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="id scenario to get testers details",
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
    public function getScenarioTesters(Request $request,ParamsHelper $paramsHelper, LoggerInterface $scenarioLogger, ValidatorInterface $validator, ResponseService $responseService,ScenarioService $scenarioService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ScenarioDetailsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->getScenariosTesters();
    }

    /**
     * @Route("/clients/list", name="api_get_all_scenarios", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function getAllClientsScenarios(ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();

        return $scenarioService->getScenarioClientList();
    }

    /**
     * @Route("/play", name="api_play_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="id scenario to get testers details",
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
    public function playScenario(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->playScenario();
    }

    /**
     * @Route("/create", name="api_create_scenario", methods={"POST"})
     * @OA\RequestBody(
     *     description="Créer un scénario",
     *     required=true
     * )
     * @OA\Response(
     *     response="201",
     *     description="Création de scénario avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Création de scénario échouée",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function createOneScenario(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new CreateScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->createScenario();
    }

    /**
     * @Route("/testers/details", name="api_testers_and_videos", methods={"GET"})
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
    public function getTestersAndVideoUrls(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StatisticsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->testersAndVideoUrlsList();
    }


    /**
     * @Route("/questions/tester/list", name="api_steps_and_answers_all_testers_by_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="ID scenario",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="test_id",
     *     in="query",
     *     description="ID scenario",
     *     required=true,
     *     @OA\Schema(type="string")
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

    public function getStepsAndAnswersAllTestersByScenario(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ListQuestionsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->questionsTesterWithIndex();
    }

    /**
     * @Route("/details/steps/details/list", name="api_get_steps_details_by_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_id",
     *     in="query",
     *     description="ID scenario",
     *     required=true,
     *     @OA\Schema(type="string")
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

    public function getStepsDetailsByScenario(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new StepsDetailsByScenarioValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->stepsDetailsByScenario();
    }

    /**
     * @Route("/{id}", name="edit_scenario", methods={"PUT"})
     */    
    public function editScenario(Scenario $scenario, Request $request, EntityManagerInterface $entityManager, ValidationErrors $validationErrors, SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', $scenario);
        $serializer->deserialize($request->getContent(), Scenario::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $scenario]);
        $entityManager->flush();

        return $this->json(['message' => 'scénario modifié avec succès'], 200);
    }

    /**
     * @Route("/{id}", name="delete_scenario", methods={"DELETE"})
     */
    public function deleteScenario(Scenario $scenario, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', $scenario);
        $entityManager->remove($scenario);
        $entityManager->flush();

        return $this->json(['message' => 'scénario supprimé avec succès'], 204);
    }

    /**
     * @Route("/validate/{id}", name="app_validate_scenario", methods={"GET"})
     * @OA\Response(
     *     response="200",
     *     description="scénario validé avec succès"
     * )
     * @OA\Response(
     *     response="401",
     *     description="Vous n\'avez pas le droit de valider le scénario"
     * )
     */
    public function validateScenario(Scenario $scenario, SerializerInterface $serializer,EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if ($user instanceof Admin) {
            $scenario->setValidate(true);
            $entityManager->persist($scenario);
            $entityManager->flush();
            return $this->json(['message' => 'scénario validé avec succès'], 200);
        }else
            return new Response('Vous n\'avez pas le droit de valider le scénario', Response::HTTP_UNAUTHORIZED);


    }

    /**
     * @Route("/close/{id}", name="validate_scenario", methods={"GET"})
     * @OA\Response(
     *     response="200",
     *     description="scénario est fermé"
     * )
     * @OA\Response(
     *     response="401",
     *     description="Vous n\'avez pas le droit de fermé le scénario"
     * )
     */
    public function closeScenario(Scenario $scenario,EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if ($user instanceof Client) {
            $scenario->setEtat(4);
            $scenario->setClosedAt(new \DateTime());
            $entityManager->persist($scenario);
            $entityManager->flush();
            return $this->json(['message' => 'scénario est fermé'], 200);
        }else
            return new Response('Vous n\'avez pas le droit de fermer le scénario', Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @Route("/duplicate/{id}", name="scenario_duplicate", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Duplicate a given scenario and his steps"
     * )
     */
    public function duplicateScenario(Scenario $scenario,SerializerInterface $serializer,EntityManagerInterface $entityManager)
    {
        $newScenario = new Scenario();
        $newScenario= clone $scenario;
        $newScenario->setTitle($scenario->getTitle().'_copie');
      
        foreach($scenario->getSteps() as $step){
            $newStep= new Step();
            $newStep = clone $step;
            $entityManager->persist($newStep);
            $newScenario->addStep($newStep);
           
        }
        $entityManager->persist($newScenario);
        $entityManager->flush();
        return $this->json(['message' => 'Scenario dupliqué avec succès','id'=>$newScenario->getId()], Response::HTTP_OK);

    }

    /**
     * @Route("/tested/ordered", name="scenarios_ordered", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="les scenario par ordre alpha croissant + nombre des testeurs qui ont déjà passer le test"
     * )
     */
    public function getScenarioTestedAtLeastOneByOrderAsc(ScenarioRepository $scenarioRepository){

        $client=$this->getUser();
        if($client instanceof Client){
            $scenarios=$scenarioRepository->findAllScenariosTestedAtLeastOne($client);
            return new JsonResponse($scenarios, Response::HTTP_OK);

        }else{
            return new Response('Vous n\'avez pas le droit d\'avoir les scénarios', Response::HTTP_UNAUTHORIZED);
        }


    }

     /**
     * @Route("/progress/{id}", name="scenarios_progress", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="l'avancement d'un scenario(nombre de test passés par rappost au total"
     * )
     */
    public function scenarioProgress(Scenario $scenario, TestRepository $testRepository, MathematicalFunctionsService $mathematicalFunctionsService)
    {
        $client=$this->getUser();
        if($client instanceof Client and $scenario->getClient()==$client){
            $tests=$testRepository->findBy(['scenario'=> $scenario, 'isAnalyzed'=>true]);
            $pourcentage= $mathematicalFunctionsService->pourcentage(count($tests),$scenario->getPanel()->getTestersNb(),100);
            return new JsonResponse($pourcentage, Response::HTTP_OK);

        }else{
            return new Response('Vous n\'avez pas le droit d\'avoir l\'avancement des scénarios', Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Route("/client/tester/list", name="api_get_all_client_tester_scenario", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function getAllClientTester(ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();

        return $scenarioService->getClientTesterWithQuestionsList();
    }

    /**
     * @Route("/tests/tester/list", name="api_get_all_tester_scenario", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function getAllTestsByTesterAndScenario(ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();

        return $scenarioService->getTestsListByTesterAndScenario();
    }

    /**
     * @Route("/admin/list", name="api_get_all_admin_scenario", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function getAllScenarioAdmin(ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();

        return $scenarioService->getAllScenarioForAdmin();
    }

    /**
     * @Route("/client/progress/list", name="api_get_all_client_scenario_with_progress", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function allClientScenariosWithProgress(ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();

        return $scenarioService->getAllClientScenariosWithProgress();
    }

    /**
     * @Route("/check/name", name="api_check_name_scenario", methods={"GET"})
     * @OA\Parameter(
     *     name="scenario_name",
     *     in="query",
     *     description="scenario name",
     *     required=true,
     *     @OA\Schema(type="json")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object",
     *         type="{ 'id': 2, 'name': 'Jihed'}"
     *     )
     * )
     */
    public function checkNameScenario(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ScenarioNameValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 201,  'scenario.empty');
        }
        return $scenarioService->getCheckNameScenario();
    }

    /**
     * @Route("/download/file/xlsx", name="api_file_scenario_tester_xlsx", methods={"POST"})
     * @OA\RequestBody(
     *     request="file Data xlsx",
     *     required=true,
     *     description="File data stat scenario xlsx",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="scenario_id", type="integer", example=1),
     *         @OA\Property(
     *             property="tester_id",
     *             type="array",
     *             @OA\Items(type="integer"),
     *             example={1, 2, 3}
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\MediaType(
     *         mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         @OA\Schema(
     *             type="string",
     *             format="binary"
     *         )
     *     )
     * )
     */
    public function downloadFileXlsx(ValidatorInterface $validator,ScenarioService $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $scenarioLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($scenarioLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new DataFileXlsxValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->getScenarioDetailsFileByTester();
    }

    /**
     * @Route("/tester/pause", name="api_pause_Scenario", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="scenario_id", type="string"),
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
    public function pauseTestersScenarios(ValidatorInterface $validator,ScenarioService  $scenarioService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $panelLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($panelLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new PauseTestersScenariosValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $scenarioService->setToPauseTestersScenarios();
    }

}

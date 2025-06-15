<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Client;
use App\Entity\ClientTester;
use App\Entity\Notifications;
use App\Entity\Panel;
use App\Entity\Scenario;
use App\Entity\Test;
use App\Entity\User;
use App\Providers\NotificationsProvider;
use App\Repository\PanelRepository;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;

class PanelServices
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private TestRepository $testRepository;
    private ResponseService $responseService;
    private TokenStorageInterface $tokenStorage;
    private $normalizer;
    private ParamsHelper $paramsHelper;
    private TestersService $testersService;
    private AnalyzeService $analyzeService;
    private $csvToClientTesters;
    private $panelInsightTesters;
    private DenormalizerInterface $denormalizer;
    private $panelRepository;
    private TranslatorInterface $translator;
    private PasswordGenerator $passwordGenerator;
    private Mailer $mailer;
    private NotificationsProvider $notificationsProvider;
    private Filesystem $filesystem;

    static array $forbiddenRoles = ["ROLE_ADMIN","ROLE_CLIENT","ROLE_TESTER"];
    private ScenarioRepository $scenarioRepository;

    public function __construct(ScenarioRepository $scenarioRepository,Filesystem $filesystem,NotificationsProvider $notificationsProvider ,PasswordGenerator $passwordGenerator,Mailer $mailer,TranslatorInterface $translator,PanelRepository $panelRepository,DenormalizerInterface $denormalizer,PanelInsightTesters $panelInsightTesters,CsvToClientTesters $csvToClientTesters,AnalyzeService $analyzeService,TestersService $testersService,TestRepository $testRepository,ParamsHelper $paramsHelper,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->paramsHelper = $paramsHelper;
        $this->testRepository = $testRepository;
        $this->testersService = $testersService;
        $this->analyzeService = $analyzeService;
        $this->csvToClientTesters = $csvToClientTesters;
        $this->panelInsightTesters = $panelInsightTesters;
        $this->denormalizer = $denormalizer;
        $this->panelRepository = $panelRepository;
        $this->translator = $translator;
        $this->passwordGenerator = $passwordGenerator;
        $this->mailer = $mailer;
        $this->notificationsProvider = $notificationsProvider;
        $this->filesystem = $filesystem;
        $this->scenarioRepository = $scenarioRepository;
    }

    public function getPanelTestersStatisticsList()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->getCurrentUser()->getUser();
            $statistics = null;
            if ($user instanceof Client) {
                $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs["scenario_id"]]);
                if (!$scenario)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
                }
                $statistics = match ($inputs["filter"]) {
                    '0' => $this->getStatsPanelCase0($scenario),
                    '1' => $this->getStatsPanelCase1($scenario),
                    '2' => $this->getStatsPanelCase2($scenario),
                    default => null
                };

                return $this->responseService->getResponseToClient($statistics);

            }else{
                return $this->responseService->getResponseToClient(null,400,"general.forbidden");
            }

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception,500,"general.500");
        }

    }

    public function panelsClient()
    {
        try {
            $user = $this->getCurrentUser()->getUser();
            if ($user instanceof Client) {
                $Allscenario = $groupedStatus = $groupedArray = $responses = array();
                $scenarios = $this->entityManager->getRepository(Scenario::class)->findBy(["client" => $user]);
                if (!$scenarios)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'panel.not_found');
                }
                $Allscenario = array_map(function ($scenario)
                {
                    if ($scenario->getPanel()) {
                        return [$scenario->getPanel()->getId(), $scenario->getTitle()];
                    }
                }, $scenarios);
                foreach ($scenarios as $scenario)
                {
                    if ($scenario->getPanel())
                    {
                        $groupedArray[$scenario->getPanel()->getId()][] = $scenario->getTitle();
                        $groupedStatus[$scenario->getPanel()->getId()][] = !($scenario->getEtat()>2);
                    }
                }
                $panelsList = $this->entityManager->getRepository(Panel::class)->findBy(["id" => array_column($Allscenario, 0)]);
                if (!$panelsList)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'panel.not_found');
                }
                foreach ($panelsList as $key => $panel)
                {
                    $responses[] = $this->serializer->normalize($panel, null, ['groups' => 'get_panel']);
                    if ($groupedArray[$panel->getId()]) {
                        $responses[$key]['scenarioName'] = $groupedArray[$panel->getId()];
                        $responses[$key]['able_edit'] = $groupedStatus[$panel->getId()][0];
                    }
                }
                return $this->responseService->getResponseToClient($responses);

            } else
            {
                return $this->responseService->getResponseToClient(null,400,"general.forbidden");
            }

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception,500,"general.500");
        }
    }

    public function createFreePanel(): JsonResponse
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(['id' => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }

                $panel = $this->denormalizer->denormalize($inputs["panel"], Panel::class, 'json',  ['groups' => 'create_panel']);
                $panelObject = $this->denormalizer->denormalize($inputs["panel"], Panel::class, 'json');
                if (count($panelObject->getClientTesters()) > 0) {
                    $clientTesterMails = array();
                    $existingUsersMails = array();
                    foreach ($panelObject->getClientTesters() as $clientTester)
                    {
                        array_push($clientTesterMails,$clientTester->getEmail());
                    }
                    $users = $this->entityManager->getRepository(ClientTester::class)->findBy(['email' => $clientTesterMails]);
                    foreach ($users as $user)
                    {
                        array_push($existingUsersMails,$user->getEmail());
                    }
                    foreach ($panelObject->getClientTesters() as $clientTester)
                    {
                        if (!in_array($clientTester->getEmail(), $existingUsersMails)) {
                            $user = new ClientTester();
                            $user->setEmail($clientTester->getEmail());
                            $user->setName($clientTester->getName());
                            $user->setLastname($clientTester->getLastName());
                            $this->entityManager->persist($user);
                        } else
                        {
                            $user = $this->getUserByEmail($clientTester->getEmail(), $users);
                        }

                        $panel->addClientTester($user);
                        $panel->addScenario($scenario);
                        $panel->setTestersNb($panelObject->getTestersNb());
                        $scenario->setEtat(2);

                        $this->entityManager->persist($panel);
                        $this->entityManager->persist($scenario);
                    }

                    $this->entityManager->flush();

                    return $this->responseService->getResponseToClient($panel->getId(),200,"panel.success_create");

                }else
                {
                    return $this->responseService->getResponseToClient(null,201,"panel.testers_empty");
                }

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function assignPanelToScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $panel = $this->entityManager->getRepository(Panel::class)->findOneBy(["id" => $inputs['panel_id']]);
            if (!$panel)
            {
                return $this->responseService->getResponseToClient(null,200,"panel.not_found");
            }
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(['id' => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
                $panel->addScenario($scenario);
                $scenario->setEtat(2);

                $this->entityManager->persist($panel);
                $this->entityManager->persist($scenario);
                $this->entityManager->flush();

            $message = $this->translator->trans('scenario.panel_assign',
                [
                    'SCENARIO' => $scenario->getTitle(),
                    'PANEL' => $panel->getName()
                ]);

                return $this->responseService->getResponseToClient($message);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function updatePanel()
    {
        return $this->responseService->getResponseToClient(null,200,"panel.success_update");
    }

    public function deletePanelById()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
                $panel = $this->entityManager->getRepository(Panel::class)->findOneBy(["id" => $inputs['panel_id']]);
                if (!$panel)
                {
                    return $this->responseService->getResponseToClient(null,404,"panel.not_found");
                }
                foreach ($panel->getScenarios() as $scenario) {
                    if ($scenario->getEtat() > 2)
                    {
                        return $this->responseService->getResponseToClient(null,201,"panel.cannot_be_deleted");
                    }else if($scenario->getEtat() == 2)
                    {
                        $scenario->setEtat(1);
                        $this->entityManager->persist($scenario);
                    }
                }
                $this->entityManager->remove($panel);
                $this->entityManager->flush();

            return $this->responseService->getResponseToClient(null, 200,"panel.success_deleted");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getStatusEmailOnCreate()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => $inputs['email']]);
            if (!$user || !in_array($user->getRoles()[0],self::$forbiddenRoles))
            {
                return $this->responseService->getResponseToClient(null,200,"mail.valid");
            }
            return $this->responseService->getResponseToClient(null,201,"mail.not_valid");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getStatusEmailOnUpdate()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => $inputs['email']]);
            if (!$user || !in_array($user->getRoles()[0],self::$forbiddenRoles))
            {
                return $this->responseService->getResponseToClient(null,200,"mail.valid");
            }
            return $this->responseService->getResponseToClient(null,201,"mail.not_valid");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function removeClientTesterFromPanelById()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $clientTester = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["id" => $inputs['client_tester_id']]);
            $panel = $this->entityManager->getRepository(Panel::class)->findOneBy(["id" => $inputs['panel_id']]);
            if (!$panel)
            {
                return $this->responseService->getResponseToClient(null,404,"panel.not_found");
            }
            if (!$clientTester)
            {
                return $this->responseService->getResponseToClient(null,404,"client_tester.not_found");
            }
            $panel->removeClientTester($clientTester);
            $panel->setTestersNb($panel->getTestersNb()-1);
            $this->entityManager->persist($panel);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient(null,200,"client_tester.deleted");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function updateClientTesterFromPanelById()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $clientTester = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["id" => $inputs['id']]);
            if (!$clientTester)
            {
                return $this->responseService->getResponseToClient(null,404,"client_tester.not_found");
            }

            $clientTester->setName($inputs['name']);
            $clientTester->setLastname($inputs['lastname']);
            $clientTester->setEmail($inputs['email']);
            $this->entityManager->persist($clientTester);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient(null,200,"client_tester.updated");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function replaceClientTesterFromPanelById()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $oldClientTester = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["id" => $inputs['current_client_tester_id']]);
            if (!$oldClientTester)
            {
                return $this->responseService->getResponseToClient(null,404,"client_tester.not_found");
            }
            $panel = $this->entityManager->getRepository(Panel::class)->findOneBy(["id" => $inputs['current_panel_id']]);
            if (!$panel)
            {
                return $this->responseService->getResponseToClient(null,404,"panel.not_found");
            }
            $panel->removeClientTester($oldClientTester);
            $this->entityManager->persist($panel);
            $this->entityManager->flush();
            $tester = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["email" => $inputs['new_email']]);
            $filteredScenarios = array_values(
                array_filter(
                    $panel->getScenarios()->map(function($element) {
                        return $element->getId();
                    })->toArray(),
                    function($id) {
                        return $id > 0;
                    }
                )
            );
            $filteredScenariosNames = array_values(
                array_filter(
                    $panel->getScenarios()->map(function($element) {
                        return $element->getTitle();
                    })->toArray(),
                    function($id) {
                        return $id != '';
                    }
                )
            );
            if ($tester)
            {
                $panel->addClientTester($tester);
                $this->replaceTester($inputs,$tester);
                $this->notificationsProvider->updateNotification($oldClientTester, $tester, $panel, $filteredScenarios);
                try {
                    $this->mailer->sendNotificationClient($tester,$filteredScenariosNames,null);
                }catch (\Exception $exception){
                    error_log("Error sending notification: " . $exception->getMessage());
                }
            }else
            {
                $tester = new ClientTester();
                $tester->setName($inputs['new_name']);
                $tester->setLastname($inputs['new_lastname']);
                $tester->setEmail($inputs['new_email']);
                $tester->setRoles(["ROLE_CLIENT_TESTER"]);
                $tester->setIsActive(true);
                $password = $this->passwordGenerator->newPassword($tester);
                $panel->addClientTester($tester);

                $this->entityManager->persist($tester);
                $this->entityManager->flush();

                $this->replaceTester($inputs,$tester);
                $this->notificationsProvider->updateNotification($oldClientTester, $tester, $panel ,$filteredScenarios);
                try {
                    $this->mailer->sendNotificationClient($tester,$filteredScenariosNames, $password);
                }catch (\Exception $exception){
                    error_log("Error sending notification: " . $exception->getMessage());
                }
            }


            return $this->responseService->getResponseToClient(null,200,"client_tester.updated");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function ClientTesterScenariosDetachment()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $oldClientTester = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["id" => $inputs['current_client_tester_id']]);
            if (!$oldClientTester)
            {
                return $this->responseService->getResponseToClient(null,404,"client_tester.not_found");
            }
            $panel = $this->entityManager->getRepository(Panel::class)->findOneBy(["id" => $inputs['current_panel_id']]);
            if (!$panel)
            {
                return $this->responseService->getResponseToClient(null,404,"panel.not_found");
            }
            $user = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["email" => $inputs['new_email']]);
            $filteredScenariosNames = array_values(
                array_filter(
                    $panel->getScenarios()->map(function($element){
                        return [
                            'id' => $element->getId(),
                            'name' => $element->getTitle(),
                        ];
                    })->toArray(),
                    function($IdNames) use ($inputs) {
                            return in_array($IdNames['id'], $inputs['scenario_id']);
                    }
                )
            );
            $scenarioNames = array_map(function($scenario) {
                return $scenario['name'];
            }, $filteredScenariosNames);
            if (!$user)
            {
                $user = new ClientTester();
                $user->setName($inputs['new_name']);
                $user->setLastname($inputs['new_lastname']);
                $user->setEmail($inputs['new_email']);
                $user->setIsActive(true);
                $user->setRoles(["ROLE_CLIENT_TESTER"]);
                $password = $this->passwordGenerator->newPassword($user);
                $user->setPassword($password);
                $panel->addClientTester($user);

                $this->entityManager->persist($user);
                $this->entityManager->persist($panel);
                $this->entityManager->flush();

                $this->mailer->sendNotificationClient($user,$scenarioNames, $password);

            } else
            {
                $panel->addClientTester($user);
                $this->entityManager->persist($panel);
                $this->entityManager->flush();
                $this->mailer->sendNotificationClient($user,$scenarioNames,null);
            }
            $tests = $this->entityManager->getRepository(Test::class)->findBy(["clientTester" => $oldClientTester,"scenario" => $inputs['scenario_id']]);
            if ($tests)
            {
                foreach ($tests as $test)
                {
                    $this->notificationsProvider->createNotification($user, $test->getScenario(),$test);
                    $test->setClientTester($user);
                    $this->entityManager->persist($test);
                    $this->entityManager->flush();
                }
            }else
            {
                return $this->responseService->getResponseToClient(null,201,"test.not_found");
            }

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getUnpassedClientTesterScenarios()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tests = $this->entityManager->getRepository(Test::class)->findBy(["clientTester" => $inputs['client_tester_id']]);
            $response = array();
            if (!$tests)
            {
                return $this->responseService->getResponseToClient(null,404,"test.not_found");
            }
            foreach ($tests as $test)
            {
                if (!$test->getIsAnalyzed() &&  ($test->getScenario()->getPanel()->getId()==$inputs['panel_id']) && $test->getScenario()->getEtat() == 3)
                {
                    array_push($response,array(
                        'test_id' => $test->getId(),
                        'scenario_name' => $test->getScenario()->getTitle(),
                        'scenario_id' => $test->getScenario()->getId()
                    ));
                }
            }

            return $this->responseService->getResponseToClient($response);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getpassedClientTesterScenarios()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tests = $this->entityManager->getRepository(Test::class)->findBy(["clientTester" => $inputs['client_tester_id']]);
            $response = array();
            if (!$tests)
            {
                return $this->responseService->getResponseToClient(null,404,"test.not_found");
            }
            foreach ($tests as $test)
            {
                if ($test->getIsAnalyzed() &&  ($test->getScenario()->getPanel()->getId()==$inputs['panel_id']))
                {
                    array_push($response,array(
                        'test_id' => $test->getId(),
                        'scenario_name' => $test->getScenario()->getTitle()
                    ));
                }
            }

            return $this->responseService->getResponseToClient($response);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function encloseAllScenariosInPanel()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenarios = $this->entityManager->getRepository(Scenario::class)->findBy(["id" => $inputs['scenario_id']]);
            $check = true;
            if (!$scenarios)
            {
                return $this->responseService->getResponseToClient(null,404,"scenario.not_found");
            }
            foreach ($scenarios as $scenario)
            {
                $check = $this->encloseTestsCheck($scenario);
            }
            if ($check)
            {
                return $this->responseService->getResponseToClient(null,404,"panel.enclose_error");
            }
            foreach ($scenarios as $scenario)
            {
                $scenario->setEtat(5);
                $scenario->setIsTested(true);
                $scenario->setProgress(100);
                $this->encloseTests($scenario);
                $this->entityManager->persist($scenario);
                $this->entityManager->flush();
            }
            return $this->responseService->getResponseToClient(null,200,"panel.success_enclose");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getDetailsPanelById()
    {
        try {
                if (!$this->getCurrentUser()->getUser() instanceof Client)
                {
                    return $this->responseService->getResponseToClient(null,400,"general.forbidden");
                }
                $inputs = $this->paramsHelper->getInputs();
                $panels = $this->entityManager->getRepository(Panel::class)->findBy(["id" => $inputs['panel_id']]);
                if (!$panels)
                {
                    return $this->responseService->getResponseToClient(null,404,"panel.not_found");
                }
                $response = $this->serializer->serialize($panels, 'json', ["groups" => "panel_details"]);
                $responseDecoded = json_decode($response)[0];
                $clienttesters = array_column(json_decode($response)[0]->clientTesters, "id");
                $scenarios = array_column(json_decode($response)[0]->scenarios, "id");
                $tests = $this->entityManager->getRepository(Test::class)->findBy(["clientTester" => $clienttesters, "scenario" => $scenarios]);

                foreach ($tests as $test) {
                    $notifications = $this->getNotificationsBulk($test->getNotifications());
                    $clientTesterId = $test->getClientTester();
                    $isAnalyzed = $test->getIsAnalyzed();
                    $this->appendDataTesters($responseDecoded,$clientTesterId,$isAnalyzed,$test,$notifications);
                }
                $modifiedResponse = json_encode($responseDecoded);
                return $this->responseService->getResponseToClient(json_decode($modifiedResponse, true));


        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function addClientTesterToPanel():JsonResponse
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $panel = $this->entityManager->getRepository(Panel::class)->findOneBy(["id" => $inputs['panel_id']]);
            if (!$panel)
            {
                return $this->responseService->getResponseToClient(null,404,"panel.not_found");
            }
            $tester = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["email" => $inputs['email']]);
            $filteredScenariosNames = array_values(
                array_filter(
                    $panel->getScenarios()->map(function($element) {
                        return $element->getTitle();
                    })->toArray(),
                    function($id) {
                        return $id != '';
                    }
                )
            );
            if ($tester)
            {
                $panel->addClientTester($tester);
                $panel->setTestersNb($panel->getTestersNb()+1);
                $this->entityManager->persist($panel);
                $this->entityManager->flush();

                try {
                    $this->mailer->sendNotificationClient($tester,$filteredScenariosNames,null);
                }catch (\Exception $exception){
                    error_log("Error sending notification: " . $exception->getMessage());
                }
            }else
            {
                $tester = new ClientTester();
                $tester->setName($inputs['name']);
                $tester->setLastname($inputs['lastname']);
                $tester->setEmail($inputs['email']);
                $tester->setRoles(["ROLE_CLIENT_TESTER"]);
                $tester->setIsActive(true);
                $password = $this->passwordGenerator->newPassword($tester);
                $panel->addClientTester($tester);
                $panel->setTestersNb($panel->getTestersNb()+1);

                $this->entityManager->persist($tester);
                $this->entityManager->persist($panel);
                $this->entityManager->flush();

                try {
                    $this->mailer->sendNotificationClient($tester,$filteredScenariosNames, $password);
                }catch (\Exception $exception){
                    error_log("Error sending notification: " . $exception->getMessage());
                }
            }
            foreach ($panel->getScenarios() as $scenario) {
                $test = new Test();
                $test->setScenario($scenario);
                $test->setClientTester($tester);
                $test->setStartedAt(new \DateTime('now'));
                $test->setEtat(0);
                $test->setIsAnalyzed(false);
                $test->setIsInterrupted(false);
                $this->entityManager->persist($test);
                $this->entityManager->flush();
                $this->notificationsProvider->createNotification($tester, $scenario,$test);

            }

            return $this->responseService->getResponseToClient(null,200,"client_tester.added");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function removeScenarioTesters()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tests = $this->entityManager->getRepository(Test::class)->findBy(['id' => $inputs["tests_id"]]);
            if (!$tests)
            {
                return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
            }
            $answers = $this->entityManager->getRepository(Answer::class)->findBy(["test" => $tests]);
            foreach($answers as $answer)
            {
                if ($answer->getVideo() != null && $this->filesystem->exists($answer->getVideo())) {
                    $this->filesystem->remove($answer->getVideo());
                }
                $this->entityManager->remove($answer);
                $this->entityManager->flush();
            }
            foreach($tests as $test) {
            $this->entityManager->remove($test);
            $this->entityManager->flush();
            }

            return $this->responseService->getResponseToClient();


        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,$exception->getMessage());
        }
    }

    public function getScenarioNotificationsAndCredentials(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenarios = $this->scenarioRepository->findBy(["id" => $inputs["scenario_id"]]);
            if (!$scenarios) {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $user = $this->entityManager->getRepository(ClientTester::class)->findOneBy(["id" => $inputs["tester_id"]]);
            if (!$user) {
                return $this->responseService->getResponseToClient(null, 404, 'users.not_found');
            }
            $test = $this->entityManager->getRepository(Test::class)->findOneBy(["scenario" => $inputs["scenario_id"],"clientTester" => $inputs["tester_id"]]);
            if (!$test) {
                return $this->responseService->getResponseToClient(null, 404, 'users.not_found');
            }

            if ($user instanceof ClientTester) {
                $scenariosNames = array_filter(array_map(function($scenario) {
                    if ($scenario->getEtat() == 3) {
                        return $scenario->getTitle();
                    }
                    return null;
                }, $scenarios));
                if (empty($scenariosNames)){
                    return $this->responseService->getResponseToClient(null,201,'scenario.scenario_not_play');
                }
                    $password = $this->passwordGenerator->newPassword($user);
                    $this->mailer->resendPlayNotificationClient($user,$scenariosNames, $password);
                    $user->setPassword($password);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }else{

                return $this->responseService->getResponseToClient(null,400,'general.forbidden');

            }
            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    private function appendDataTesters($responseDecoded,$clientTesterId,$isAnalyzed,$test,$notifications)
    {
        foreach ($responseDecoded->clientTesters as &$clientTester) {
            if ($clientTester->id == $clientTesterId->getId()) {
                if ($isAnalyzed) {
                    $clientTester->tests_passed[] = $test->getId();
                    $clientTester->tests_pending[] = null;
                } else {
                    $clientTester->tests_passed[] = null;
                    $clientTester->tests_pending[] = $test->getId();
                }
                $clientTester->tests_passed = array_filter($clientTester->tests_passed);
                $clientTester->tests_pending = array_filter($clientTester->tests_pending);
                if (isset($notifications[$test->getId()])) {
                    $clientTester->notification = (string)$notifications[$test->getId()][0];
                }else
                {
                    $clientTester->notification = "0";
                }

            }
        }
    }

    private function getNotificationsBulk($notifications)
    {
        $notifs = array();
        foreach ($notifications as $notification)
        {
            $notifs[$notification->gettest()->getId()][] = $notification->getNotificationNumber();
        }
        return $notifs;
    }

    private function getStatsPanelCase0($scenario)
    {
        if($scenario->getPanel()->getType() === 'client')
        {
            $tests = $this->testRepository->findBy(['scenario' => $scenario, 'etat' => 2 ]);
        }else{
            $tests = $this->testRepository->findBy(['scenario' => $scenario, 'etat' => 2 ,'clientTester'=> null]);
        }
        $ids = array_map(function($data){
            return $data->getTester()??$data->getClientTester();
            },$tests);
       return $this->testersService->statistics(
           array_filter(array_unique($ids)
           ),
           $scenario->getPanel()->getType()
       );
    }

    private function getStatsPanelCase1($scenario)
    {
        $datas = $this->analyzeService->journeyMapByTests($scenario);
        $idps = [];
        foreach($datas as $data)
        {
            if ($data['average']>=0 and $data['average']!= null ){
                array_push($idps,(int)trim($data['labels'],'T'));
            }
        }
        return $this->testersService->statistics(array_filter(array_unique($idps)),$scenario->getPanel()->getType());
    }

    private function getStatsPanelCase2($scenario)
    {
        $datas = $this->analyzeService->journeyMapByTests($scenario);
        $idns=[];
        foreach($datas as $data)
        {
            if($data['average']<0)
            {
                array_push($idns,(int)trim($data['labels'],'T'));
            }
        }
       return $this->testersService->statistics(array_filter(array_unique($idns)),$scenario->getPanel()->getType());
    }


    private function getUserByEmail($email, $users) {
        foreach ($users as $user) {
            if ($email == $user->getEmail()) {
                return $user;
            }
        }
        return null;
    }

    private function replaceTester($inputs,$tester)
    {
        $scenarios = $this->entityManager->getRepository(Scenario::class)->findBy(["panel" => $inputs['current_panel_id']]);
        $tests = $this->entityManager->getRepository(Test::class)->findBy(["clientTester" => $inputs['current_client_tester_id'],"scenario" => $scenarios]);
        foreach ($tests as $test)
        {
            $test->setClientTester($tester);
            $this->entityManager->persist($tester);
            $this->entityManager->flush();
        }
    }

    public function encloseTests($scenario)
    {
        foreach ($scenario->getTests() as $test)
        {
            $test->setIsAnalyzed(true);
            $test->setEtat(2);
            $this->entityManager->persist($test);
            $this->entityManager->flush();
        }
    }

    private function encloseTestsCheck($scenario): bool
    {
        static $check = true;
        foreach ($scenario->getTests() as $test)
        {
            if ($test->getIsAnalyzed())
            {
                $check = false;
            }
        }
        return $check;
    }
    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }

}
<?php

namespace App\Service;

use App\Entity\Admin;
use App\Entity\Answer;
use App\Entity\Client;
use App\Entity\ClientTester;
use App\Entity\Notifications;
use App\Entity\Scenario;
use App\Entity\SubClient;
use App\Entity\Test;
use App\Entity\Tester;
use App\Providers\NotificationsProvider;
use App\Repository\PanelRepository;
use App\Repository\ScenarioRepository;
use App\Repository\TesterRepository;
use App\Repository\TestRepository;
use App\Repository\ClientTesterRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Utils\ColumnProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScenarioService
{
    private $entityManager;
    private $scenarioRepository;
    private $serializer;
    private $testRepository;
    private $mathematicalFunctionsService;
    private $responseService;
    private $tokenStorage;
    private $normalizer;
    private $paramsHelper;
    private $analyzeService;
    private $mailer;
    private $passwordGenerator;
    private $denormalizer;
    private $notificationsProvider;
    private $testerRepository;
    private $clientTesterRepository;
    private $columnProvider;
    private $translator;


    public function __construct(TranslatorInterface $translator,ColumnProvider $columnProvider,ClientTesterRepository $clientTesterRepository,TesterRepository $testerRepository,NotificationsProvider $notificationsProvider,DenormalizerInterface $denormalizer,PasswordGenerator $passwordGenerator,Mailer $mailer,AnalyzeService $analyzeService,ParamsHelper $paramsHelper ,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, ScenarioRepository $scenarioRepository, SerializerInterface $serializer,TestRepository $testRepository,PanelRepository $panelRepository, MathematicalFunctionsService $mathematicalFunctionsService)
    {
        $this->entityManager = $entityManager;
        $this->scenarioRepository = $scenarioRepository;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->mathematicalFunctionsService = $mathematicalFunctionsService;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->paramsHelper = $paramsHelper;
        $this->analyzeService = $analyzeService;
        $this->mailer = $mailer;
        $this->passwordGenerator = $passwordGenerator;
        $this->notificationsProvider = $notificationsProvider;
        $this->testerRepository = $testerRepository;
        $this->clientTesterRepository = $clientTesterRepository;
        $this->columnProvider = $columnProvider;
        $this->translator = $translator;
    }
    public function getScenarioClientList()
    {
        try {
            $user = $this->getCurrentUser()->getUser();
            if ($user instanceof Client) {
                $scenarios = $this->scenarioRepository->findBy(["client" => $user, "etat" => [1,2,3,4,5,6]],["createdAt" => "DESC"]);
                if (!$scenarios)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
                }
                foreach ($scenarios as $scenario)
                {
                    if ($scenario->getEtat()==4 and $scenario->getStartedAt()!= null)
                    {
                        $date = new \DateTime();
                        if($date->sub(new \DateInterval('P7D'))->getTimestamp()-$scenario->getStartedAt()->getTimestamp()>0){
                            $scenario->setEtat(1);
                        }
                    }
                    if($scenario->getPanel()!=null)
                    {
                        $tests = $this->testRepository->findBy(['scenario'=> $scenario, 'isAnalyzed'=>true]);
                        $pourcentage = $this->mathematicalFunctionsService->pourcentage(count($tests),$scenario->getPanel()->getTestersNb(),100);
                        if ($scenario->getEtat() == 5)
                        {
                            $scenario->setProgress(100);
                        }elseif ($pourcentage == 100){
                            $scenario->setProgress($pourcentage);
                            $scenario->setEtat(4);
                        }
                        else{
                            $scenario->setProgress($pourcentage);
                        }
                    }
                    $this->entityManager->persist($scenario);
                    $this->entityManager->flush();
                }
            }else
            {
                return $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }
            $unsortedResponses = $this->serializer->serialize($scenarios, 'json', ['groups' => 'view_scenario']);
            $responses = json_decode($unsortedResponses,true);
            foreach ($responses as $key=>$response)
            {
                usort($responses[$key]["steps"], function($a, $b)
                {
                    if (isset($a['number']) && isset($b['number'])) {
                        return $a['number'] - $b['number'];
                    }
                    return 0;
                });
            }

            return $this->responseService->getResponseToClient($responses);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }

    }

    public function getClientTesterWithQuestionsList()
    {
        try {
            $user = $this->getCurrentUser()->getUser();
            if ($user instanceof ClientTester) {
                $scenarios = $this->scenarioRepository->findScenariosByClientTester($user);
                if (!$scenarios)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
                }
                $scenarioIds = [];
                foreach ($scenarios as $scenario) {
                    $scenarioIds[] = $scenario->getId();
                }
                $tests = $this->testRepository->findBy(['clientTester' => $user, 'scenario' => $scenarioIds]);
                if (!$tests)
                {
                    return $this->responseService->getResponseToClient(null, 404, 'test.not_found');
                }
                $responses = $this->serializer->serialize($scenarios, 'json', ['groups' => 'view_scenario']);
                $response = json_decode($responses, true);
                usort($response[0]["steps"], function($a, $b)
                {
                    if (isset($a['number']) && isset($b['number'])) {
                        return $a['number'] - $b['number'];
                    }
                    return 0;
                });
                $newResponse = $this->addStatusTestToScenario(json_encode($response, true), $tests);
                return $this->responseService->getResponseToClient($newResponse);
            }else
            {
                return $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getTestsListByTesterAndScenario()
    {
        try {
            $user = $this->getCurrentUser()->getUser();
            $etat = array();
            if ($user instanceof Tester){
                $scenarios = $this->scenarioRepository->findScenariosByTester($user);
                $scenarioIds = [];
                foreach ($scenarios as $scenario) {
                    $scenarioIds[] = $scenario->getId();
                }
                $tests = $this->testRepository->findBy(['tester' => $user, 'scenario' => $scenarioIds]);
                $response = $this->serializer->serialize($scenarios, 'json', ['groups' => 'view_scenario']);
                $newResponse = $this->addStatusTestToScenario($response, $tests);

                return $this->responseService->getResponseToClient($newResponse);
            }else
            {
                return $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getAllScenarioForAdmin()
    {
        try {
            $user = $this->getCurrentUser()->getUser();
            if($user instanceof Admin){
                $scenarios = $this->scenarioRepository->findAllScenarios();
                $response = $this->serializer->serialize($scenarios, 'json', ['groups' => 'view_scenario']);

                return $this->responseService->getResponseToClient(json_decode($response,true));
            }else
            {
                return $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getAllClientScenariosWithProgress()
    {
        try {
            $user = $this->getCurrentUser()->getUser();
            if($user instanceof Client){
                $scenarios = $this->scenarioRepository->findByClientAndProgress($user);
                $response = $this->serializer->serialize($scenarios, 'json', ['groups' => 'alldata_select']);

                return $this->responseService->getResponseToClient(json_decode($response,true));
            }else
            {
                return $this->responseService->getResponseToClient(null, 401, 'general.forbidden');
            }
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getScenariosDetails()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenarios = $this->scenarioRepository->findOneBy(["id" => $inputs["id"]]);
            if (!$scenarios)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $avg_score = 0;
            $avg_duration = 0;
            $testers_done = 0;
            $serialized_data = $this->serializer->serialize($scenarios, 'json', ['groups' => 'scenario_details']);
            $data = json_decode($serialized_data , true);
            if (!isset($data['steps']) || !$data)
            {
                return $this->responseService->getResponseToClient(null, 404, 'step.not_found');
            }
            foreach ($data['steps'] as $step)
            {
                $avg_score += $this->getAVGScore($step);
                $avg_duration += $this->getAVGDuration($step);
            }
            $response = array(
                "id" => $data['id'],
                "title" => $data['title'],
                "testersDone" => $this->getTestersDone($data),
                "testers" => $data['panel']['testersNb'],
                "steps" => count($data['steps']),
                "type" => $data['panel']['type'],
                "score" => $this->truncateToTwoDecimals($avg_score/count($data['steps'])),
                "duration" => $this->truncateToTwoDecimals($avg_duration/count($data['steps']))
            );

            return $this->responseService->getResponseToClient($response);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getScenariosTesters()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $testers = [];
            $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            foreach ($scenario->getTests() as $test) {
                $testers[] = $test->getTester()?? $test->getClientTester();
            }
            $response = $this->serializer->serialize($testers, 'json', ['groups' => 'tester_id']);

            return $this->responseService->getResponseToClient(json_decode($response,true));
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function createScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->getCurrentUser()->getUser();
            if ($user instanceof SubClient && !$user->getWriteRights()) {
                return $this->responseService->getResponseToClient(null, 401, 'scenario.forbidden');
            }
            $inputs['isUnique'] = (bool)($inputs['isUnique']);
            $inputs['isModerate'] = (bool)($inputs['isModerate']);
            $scenario = $this->denormalizer->denormalize($inputs, Scenario::class, 'json');
            $scenario->setClient($this->getClient($user));
            $scenario->setValidate(false);
            $scenario->setEtat(0);

            $this->entityManager->persist($scenario);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient(["message" => 'Scénario créé', 'id' => $scenario->getId()]);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function playScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $user = $this->getCurrentUser()->getUser();
            if ($user instanceof Client) {
                if($scenario->getPanel()->getType() == 'client')
                {
                    foreach ($scenario->getPanel()->getClientTesters() as $clientTester) {
                        $clientTester->setIsActive(true);
                        $clientTester->setRoles(['ROLE_CLIENT_TESTER']);
                        if($clientTester->getPassword())
                        {
                            $this->mailer->sendPlayNotificationClient($clientTester,$scenario);
                        }else{
                            $password = $this->passwordGenerator->newPassword($clientTester);
                            $this->mailer->sendPlayNotificationClient($clientTester,$scenario, $password);
                        }
                        $test = new Test();
                        $test->setScenario($scenario);
                        $test->setEtat(0);
                        $test->setClientTester($clientTester);

                        $this->notificationsProvider->createNotification($clientTester,$scenario,$test);

                        $this->entityManager->persist($clientTester);
                        $this->entityManager->persist($test);
                    }
                }else{
                    foreach ($scenario->getPanel()->getInsightTesters() as $insightTester) {
                        $insightTester->setIsActive(true);
                        $insightTester->setRoles(['ROLE_TESTER']);
                        $test = new Test();
                        $test->setScenario($scenario);
                        $test->setEtat(0);
                        $test->setTester($insightTester);
                        $this->mailer->sendNotification($insightTester, $scenario);

                        $this->entityManager->persist($insightTester);
                        $this->entityManager->persist($test);

                    }
                }
                $scenario->setStartedAt(new \DateTime());
                $scenario->setEtat(3);

                $this->entityManager->persist($scenario);
                $this->entityManager->flush();

                return $this->responseService->getResponseToClient(null,200,'scenario.success_lunch');

            }else
            {
                return $this->responseService->getResponseToClient(null,201,'scenario.forbidden');
            }
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function testersAndVideoUrlsList()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tests = $this->testRepository->findBy(["scenario" => $inputs["scenario_id"], "isAnalyzed" => true]);
            if (!$tests)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $response = $this->serializer->serialize($tests, 'json', ['groups' => 'tester_video']);

            return $this->responseService->getResponseToClient(json_decode($response,true));

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function questionsTesterWithIndex()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->getCurrentUser()->getUser();
            $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $stepIds = [];
            foreach ($scenario->getSteps() as $step) {
                $stepIds[] = $step->getId();
            }
            $answersNb = $this->entityManager->getRepository(Answer::class)->findBy(['clientTester' => $user, 'step' => $stepIds,"test" => $inputs["test_id"]]);
            $questionsList = $this->serializer->serialize($scenario, 'json', ['groups' => 'view_scenario']);
            $response = json_decode($questionsList, true);
            $response["index_answers"] = count($answersNb);
            usort($response["steps"], function($a, $b)
            {
                if (isset($a['number']) && isset($b['number'])) {
                    return $a['number'] - $b['number'];
                }
                return 0;
            });

            return $this->responseService->getResponseToClient($response);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function stepsDetailsByScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $response = $this->serializer->serialize($scenario, 'json', ['groups' => 'view_scenario']);
            $finalResponse = json_decode($response, true);
            usort($finalResponse["steps"], function($a, $b)
            {
                if (isset($a['number']) && isset($b['number'])) {
                    return $a['number'] - $b['number'];
                }
                return 0;
            });
            return $this->responseService->getResponseToClient($finalResponse);


        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }

    }

    public function getCheckNameScenario()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $pattern = '/[!@#$%^&*()+=²[\]{}|\\\\:;\'",<>?\/`~]/';
            if (preg_match($pattern, strtolower($inputs["scenario_name"])))
            {
                return $this->responseService->getResponseToClient(null, 201, 'scenario.special_char');
            }
            $scenario = $this->scenarioRepository->findOneBy(["title" => strtolower($inputs["scenario_name"])]);
            if ($scenario)
            {
                return $this->responseService->getResponseToClient(null, 201, 'scenario.already_exist');
            }
            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getScenarioDetailsFileByTester()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $testers = $this->clientTesterRepository->findBy(["id" => $inputs["testers_id"]]);
            if (!$testers)
            {
                return $this->responseService->getResponseToClient(null, 404, 'client_tester.not_found');
            }
            $scenario = $this->scenarioRepository->findOneBy(["id" => $inputs["scenario_id"]]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $title = $scenario->getTitle();
            if (strlen($title) > 31) {
                $title = substr($title, 0, 31);
            }
            $streamedResponse = new StreamedResponse();
            $streamedResponse->setCallback(function () use ($testers,$title,$scenario) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray($this->columnProvider->getColumn(), null, 'A1');
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
                $counter = 2;
                foreach ($testers as $tester)
                {
                    $sheet->getDefaultColumnDimension()->setWidth(20);
                    $sheet->getColumnDimension('G')->setWidth(80);
                    $sheet->getColumnDimension('H')->setWidth(80);
                    $sheet->getColumnDimension('I')->setWidth(50);
                    $sheet->setTitle($title);
                    $counter = $this->buildDataExport($scenario, $sheet,$tester,$counter);
                }
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                    exit();
            });

            $streamedResponse->setStatusCode(Response::HTTP_OK);
            $streamedResponse->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="' . $title . '.xlsx"');
            // Add CORS headers
            $streamedResponse->headers->set('Access-Control-Allow-Origin', '*');
            $streamedResponse->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $streamedResponse->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');

            $streamedResponse->send();
            return $streamedResponse;

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function buildDataExport($scenario, $sheet, $tester, $counter)
    {
        $steps = $scenario->getSteps();
        $data = [];
        foreach ($steps as $step) {
            $answer = $this->getSingleAnswer($step->getAnswers(),$step,$tester);
            $data [] = [
                'title' => $scenario->getTitle(),
                'step_number' => $step->getNumber(),
                'name' => $tester->getName(),
                'lastname' => $tester->getLastname(),
                'email' => $tester->getEmail(),
                'type' => $step->getType(),
                'question' => $step->getQuestion(),
                'answer' => $answer?->getAnswer(),
                'comment' => $answer?->getComment(),
                'not_interesting' => $step->getType() == 'scale' ? 'Pas Intéressant' : '',
                'very_interesting' => $step->getType() == 'scale' ? 'Très Interessant' : '',
                'min_scale' => $step->getQuestionChoices()?->getMinScale(),
                'max_scale' => $step->getQuestionChoices()?->getMaxScale(),
            ];
        }

        usort($data, function ($a, $b) {
            return $a['step_number'] <=> $b['step_number'];
        });
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $counter, $row['title']);
            $sheet->getStyle('A' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('B' . $counter, $row['step_number']);
            $sheet->getStyle('B' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('C' . $counter, $row['name']);
            $sheet->getStyle('C' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('D' . $counter, $row['lastname']);
            $sheet->getStyle('D' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('E' . $counter, $row['email']);
            $sheet->getStyle('E' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('F' . $counter, $row['type']);
            $sheet->getStyle('F' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('G' . $counter, $row['question']);
            $sheet->getStyle('G' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('H' . $counter, $row['answer']);
            $sheet->getStyle('H' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('I' . $counter, $row['comment']);
            $sheet->getStyle('I' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('J' . $counter, $row['not_interesting']);
            $sheet->getStyle('J' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('K' . $counter, $row['very_interesting']);
            $sheet->getStyle('K' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('L' . $counter, $row['min_scale']);
            $sheet->getStyle('L' . $counter)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('M' . $counter, $row['max_scale']);
            $sheet->getStyle('M' . $counter)->getAlignment()->setWrapText(true);

            $sheet->getRowDimension($counter)->setRowHeight(30);
            $counter++;
        }
        return $counter;
    }

    private function getSingleAnswer($answers,$step,$tester){
        foreach ($answers as $answer) {
            if ($answer?->getStep()?->getId() == $step->getId() && $answer?->getClientTester()?->getId() == $tester->getId()) {
                return $answer;
            }
        }
        return null;
    }
    public function setToPauseTestersScenarios()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(["id" => $inputs['scenario_id']]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null,404,"scenario.not_found");
            }
            if ($scenario->getEtat() == 3){
                $scenario->setEtat(6);
                $this->pauseAndPlayTests($scenario,1);
                $this->entityManager->persist($scenario);
                $this->entityManager->flush();
                $message = $this->translator->trans('scenario.scenario_pause', [
                    'SCENARIO' => $scenario->getTitle()
                ]);

            }elseif ($scenario->getEtat() == 6){
                $scenario->setEtat(3);
                $this->pauseAndPlayTests($scenario,0);
                $this->entityManager->persist($scenario);
                $this->entityManager->flush();
                $message = $this->translator->trans('scenario.scenario_play', [
                    'SCENARIO' => $scenario->getTitle()
                ]);
            }

            return $this->responseService->getResponseToClient($message);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function pauseAndPlayTests($scenario,$status)
    {
        foreach ($scenario->getTests() as $test)
        {
            $test->setEtat($status);
            $this->entityManager->persist($test);
            $this->entityManager->flush();
        }
    }
    private function addStatusTestToScenario($response, $tests)
    {
        $responseArray = json_decode($response, true);
        foreach ($tests as $test)
        {
            $scenarioId = $test->getScenario()->getId();
            $scenarioTestStatus[$scenarioId] = [
                "test_status" => $test ? $test->getEtat() : null,
                "test_id" => $test ? $test->getId() : null,
                "isInterrupted" =>  $test->getIsInterrupted() != null? $test->getIsInterrupted() : false
            ];
        }
        foreach ($responseArray as $key=>$scenario) {
            $scenarioId = $scenario['id'];
            if (isset($scenarioTestStatus[$scenarioId])) {
                $responseArray[$key]['test_status'] = $scenarioTestStatus[$scenarioId]["test_status"];
                $responseArray[$key]['test_id'] = $scenarioTestStatus[$scenarioId]["test_id"];
                $responseArray[$key]['isInterrupted'] = $scenarioTestStatus[$scenarioId]["isInterrupted"];
            }
        }

        return  $responseArray;
    }
    private function getClient(UserInterface $user)
    {
        if ($user instanceof SubClient) {
            $client = $user->getClient();
        } elseif ($user instanceof Client) {
            $client = $user;
        }

        return $client;
    }
    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }

    private function getAVGScore($step)
    {
        $total_score = 0;
        $count_scores = 0;
        foreach ($step['answers'] as $answer) {
            $total_score += (float)$answer['score'];
            $count_scores++;
        }
        if ($count_scores > 0) {
            return $total_score / $count_scores;
        } else {
            return 0;
        }
    }
    private function getAVGDuration($step)
    {
        $total_duration = 0;
        $count_duration = 0;
        foreach ($step['answers'] as $answer) {
            $total_duration += (float)$answer['duration'];
            $count_duration++;
        }
        if ($count_duration > 0) {
            return $total_duration / $count_duration;
        } else {
            return 0;
        }
    }

    private function getTestersDone($data)
    {
        if (isset($data['steps'][0]) && isset($data['steps'][0]["answers"])){
            return count($data['steps'][0]["answers"]);
        }
        return 0;
    }

    private function truncateToTwoDecimals($number)
    {
        return substr($number, 0, strpos($number, '.') + 3);
    }
}
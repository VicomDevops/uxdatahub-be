<?php

namespace App\Service;

use App\Entity\Test;
use App\Providers\WebSitesImagesProvider;
use App\Service\ServiceProviders\FileParserFactory;
use App\Utils\ParamsHelper;
use App\Service\ResponseService;
use App\Service\ServiceProviders\OpenAIServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Dompdf\Dompdf;
use Symfony\Component\Serializer\SerializerInterface;

class OpenAIService
{
    private ResponseService $responseService;
    private ParamsHelper $paramsHelper;
    private OpenAIServiceProvider $openAIServiceProvider;
    private TranslatorInterface $translator;
    private Environment $twig;
    private FileParserFactory $fileParserFactory;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;


    public function __construct(SerializerInterface $serializer,EntityManagerInterface $entityManager,FileParserFactory $fileParserFactory,Environment $twig,TranslatorInterface $translator,OpenAIServiceProvider $openAIServiceProvider,ParamsHelper $paramsHelper , ResponseService $responseService)
    {
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->openAIServiceProvider = $openAIServiceProvider;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->fileParserFactory = $fileParserFactory;
        $this->entityManager = $entityManager;
        $this->serializer  = $serializer;
    }
    public function getAnalysesFromOpenAI(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $result = $this->openAIServiceProvider->getChatGPTAnalyses($inputs['messages']);
            $response = json_decode($result, true);
            $finalResponse = $response['choices'][0]['message']['content'];
            return $this->responseService->getResponseToClient($finalResponse);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getAnalysesFileFromOpenAI(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $fileExtension = $inputs['file']->getClientOriginalExtension();
            $parser = $this->fileParserFactory->create($fileExtension);
            $filePath = $inputs['file']->getPathname();
            $parsedContent = $parser->extractContent($filePath);
            $inputString = $this->openAIServiceProvider->getChatGPTAnalyses($this->translator->trans('ux_audit_alldata_flash.alldata_flash').json_encode($parsedContent, JSON_PRETTY_PRINT));
            $urlImage = WebSitesImagesProvider::findImageWithSpecificDimensions($inputs["url"],1080,608);

            if (!$this->isValidUrl($urlImage)){
                $description = $this->openAIServiceProvider->getChatGPTAnalyses( $this->translator->trans('audit_ux_flash.entity_description', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]));
                $dataImage = $this->openAIServiceProvider->createAIChatGPTImagesProvider($description['choices'][0]['message']['content']);
                $image = $dataImage['data'][0]['b64_json'];
            }else{
                $image = WebSitesImagesProvider::imageToBase64($urlImage);
            }

            $recommendations = preg_split('/\n\n(?=\d+\. Préconisations)/', $inputString['choices'][0]['message']['content']);
            $allRecommendations = [];

            foreach ($recommendations as $recommendation) {
                preg_match('/Préconisations = (.+)/', $recommendation, $preconisation);
                preg_match('/Étape = (\d+)/', $recommendation, $etape); 
                preg_match('/Citations = (.+)/', $recommendation, $citation);
                preg_match('/Nombre d\'utilisateurs = (.+)/', $recommendation, $nbUtilisateurs);
                preg_match('/Argument = (.+)/', $recommendation, $argument);

                $allRecommendations[] = [
                    'preconisations' => str_replace(['\\', '"'], '', $preconisation[1]),
                    'step' => isset($etape[1]) ? str_replace(['\\', '"'], '', $etape[1]) : '',
                    'citation' => str_replace(['\\', '"'], '', $citation[1]) ?? '',
                    'users_number' => $nbUtilisateurs[1] ?? '',
                    'argument' => str_replace(['\\', '"'], '', $argument[1]) ?? ''
                ];
            }
            $allRecommendations[] = ['image' => $image];

            return $this->responseService->getResponseToClient($allRecommendations);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getAuditUXFlash(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $data = [
                'entity_description' => $this->translator->trans('audit_ux_flash.entity_description', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'entity_images' => $this->translator->trans('audit_ux_flash.entity_images', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'entity_adventages' => $this->translator->trans('audit_ux_flash.entity_adventages', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'main_entity_note' => $this->translator->trans('audit_ux_flash.main_entity_note', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'entity_strong_points' => $this->translator->trans('audit_ux_flash.entity_strong_points', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'entity_weak_points' => $this->translator->trans('audit_ux_flash.entity_weak_points', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'entity_brief_recommendations' => $this->translator->trans('audit_ux_flash.entity_brief_recommendations', ['ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'zoom_on_the_competitors_1' => $this->translator->trans('audit_ux_flash.entity_zoom_on_the_competitors', ['COMPETITION' => $inputs["competingUrl1"],'ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'note_the_competitors_1' => $this->translator->trans('audit_ux_flash.entity_note_the_competitors', ['ENTITY' => $inputs["competingUrl1"], 'WORKFIELD' => $inputs['workField']]),
                'zoom_on_the_competitors_title_1' => $this->translator->trans('audit_ux_flash.entity_zoom_on_the_competitors_title', ['ENTITY' => $inputs["competingUrl1"], 'WORKFIELD' => $inputs['workField']]),
                'zoom_on_the_competitors_2' => $this->translator->trans('audit_ux_flash.entity_zoom_on_the_competitors', ['COMPETITION' => $inputs["competingUrl2"],'ENTITY' => $inputs["url"], 'WORKFIELD' => $inputs['workField']]),
                'note_the_competitors_2' => $this->translator->trans('audit_ux_flash.entity_note_the_competitors', ['ENTITY' => $inputs["competingUrl2"], 'WORKFIELD' => $inputs['workField']]),
                'zoom_on_the_competitors_title_2' => $this->translator->trans('audit_ux_flash.entity_zoom_on_the_competitors_title', ['ENTITY' => $inputs["competingUrl2"], 'WORKFIELD' => $inputs['workField']]),
            ];
            $urlImage = WebSitesImagesProvider::findImageWithSpecificDimensions($inputs["url"],1080,608);
            foreach ($data as $key => $trans) {
                if (str_contains($key, "entity_images")){
                    if (!$this->isValidUrl($urlImage)){
                        $description = $this->openAIServiceProvider->getChatGPTAnalyses($data["entity_description"]);
                        $dataImage = $this->openAIServiceProvider->createAIChatGPTImagesProvider($description['choices'][0]['message']['content']);
                        $response = $dataImage['data'][0]['b64_json'];
                    }else{
                        $response = WebSitesImagesProvider::imageToBase64($urlImage);
                    }
                }else{
                    $response = $this->openAIServiceProvider->getChatGPTAnalyses($trans);
                }
                $responses[$key] = match (true) {
                    str_contains($key, "entity_adventages") => $this->SplitEntityAdventages('Ses avantages concurrentiels', $response['choices'][0]['message']['content']),
                    str_contains($key, "entity_brief_recommendations") => $this->extractKeyValuePairs($response['choices'][0]['message']['content']),
                    str_contains($key, "entity_weak_points"), str_contains($key, "entity_strong_points") => $this->SplitString($response['choices'][0]['message']['content']),
                    str_contains($key, "entity_images") => $response,
                    default => $response['choices'][0]['message']['content'],
                };
            }
            foreach ($inputs as $key => $value) {
                $responses[$key] = $value;
            }

            return $this->responseService->getResponseToClient($responses);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getTargetedRecommendationsByStepFromOpenAI()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenarioData = $this->entityManager->getRepository(Test::class)->findBy(["scenario" => $inputs["scenario_id"], 'isAnalyzed' => true]);
            if (!$scenarioData)
            {
                return $this->responseService->getResponseToClient(null, 404, 'scenario.not_found');
            }
            $response = $this->serializer->serialize($scenarioData, 'json', ['groups' => 'targeted_recommendations']);
            $sortedData = [];
            $data = json_decode($response, true);
            foreach ($data as $scenario) {
                if (isset($scenario['answers'])) {
                    foreach ($scenario['answers'] as $answer) {
                        $stepNumber = $answer['step']['number'];
                        $clientName = $answer['clientTester']['name'] . ' ' . $answer['clientTester']['lastname'];
                        if (!isset($sortedData[$stepNumber])) {
                            $sortedData[$stepNumber] = [
                                'step_details' => [
                                    'question' => $answer['step']['question'],
                                    'URL' => $answer['step']['url'],
                                    'scenario_name' => $scenario['scenario']['title']
                                ],
                                'answers' => [],
                                'total_score' => 0,
                                'total_duration' => 0,
                                'count' => 0
                            ];
                        }
                        $duration = is_numeric($answer['duration']) ? floatval($answer['duration']) : 0;
                        $sortedData[$stepNumber]['answers'][] = [
                            'client_name' => $clientName,
                            'answer' => $answer['answer'],
                            'comment' => $answer['comment'],
                            'score' => $answer['score'],
                            'duration' => $duration
                        ];
                        $sortedData[$stepNumber]['total_score'] += $answer['score'];
                        $sortedData[$stepNumber]['total_duration'] += $duration;
                        $sortedData[$stepNumber]['count']++;
                    }
                }
            }
            foreach ($sortedData as $stepNumber => &$stepData) {
                $count = $stepData['count'];
                $stepData['average_score'] = $count > 0 ? $stepData['total_score'] / $count : 0;
                $stepData['average_duration'] = $count > 0 ? $stepData['total_duration'] / $count : 0;
                $stepData['step'] = $stepNumber;
                unset($stepData['total_score'], $stepData['total_duration'], $stepData['count']);
            }
            ksort($sortedData);
            $response = $this->getOpenAITargetedRecommendations($sortedData);

            return $this->responseService->getResponseToClient(array_values($response));
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function getConcreteRecommendationsByStepFromOpenAI()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $formattedData = $this->readDataFromXLSX($inputs);
            $which_quick_wins = $this->translator->trans('concrete_recommendations.which_quick_wins', ['ENTITY' => json_encode($formattedData)]);
            $topwins = $this->openAIServiceProvider->getChatGPTAnalyses($which_quick_wins);
            $lines = explode("\n", $topwins['choices'][0]['message']['content']);
            $stepsFilter = $this->filterEmails($lines);
            $filteredTesters = $this->matchFiltredData($stepsFilter,$formattedData);
            $response = [];
            foreach ($filteredTesters as $filteredTester) {
                $preconisation = $this->openAIServiceProvider->getChatGPTAnalyses($this->translator->trans('concrete_recommendations.quick_wins_recommendations', ['ENTITY' => json_encode($filteredTester)]));
                preg_match('/Préconisations\s*=\s*(.+?)•\s*Argument\s*=\s*(.+)$/', $preconisation['choices'][0]['message']['content'], $matches);
                preg_match('/^(\w+)\s+(\w)/', $filteredTester['name'].' '.$filteredTester['lastName'], $name);
                $response[]=[
                'preconisations' => trim($matches[1]),
                'step' => $filteredTester['step'],
                'testerName' => $name?"{$name[1]} .".strtoupper($name[2]):$filteredTester['name'].' '.$filteredTester['lastName'],
                'citations' => $filteredTester['testerResponse'],
                'testerNumber' => $filteredTester['users_number'],
                'argument' => trim($matches[2])
                ];
            }
            return $this->responseService->getResponseToClient($response);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    private function filterEmails($lines){
        $stepsFilter = [];
        foreach ($lines as $line) {
            if (preg_match('/([\w\.]+@[\w\.]+), Étape (\d+), (\d+) utilisateur/', $line, $matches)) {
                $stepsFilter[] = [
                    'email' => $matches[1],
                    'step' => $matches[2],
                    'users_number' => $matches[3]
                ];
            }
        }
        return $stepsFilter;
    }
    private function matchFiltredData($stepsFilter,$formattedData)
    {
        $filteredTesters = [];
        foreach ($stepsFilter as $filter) {
            foreach ($formattedData as $tester) {
                if ($tester["testerData"]["email"] === $filter["email"]) {
                    foreach ($tester["steps"] as $step) {
                        if ($step["stepNumber"] === $filter["step"]) {
                            $filteredTesters[] = [
                                "email" => $tester["testerData"]["email"],
                                "name" => $tester["testerData"]['name'],
                                "lastName" => $tester["testerData"]["lastName"],
                                "step" => $step["stepNumber"],
                                "question" => $step["question"],
                                "testerResponse" => $step["testerResponse"],
                                "comment" => $step["comment"],
                                "lowerBoundLabel" => $step["lowerBoundLabel"],
                                "upperBoundLabel" => $step["upperBoundLabel"],
                                "min" => $step["min"],
                                "max" => $step["max"],
                                "users_number" => $filter["users_number"],
                            ];
                        }
                    }
                }
            }
        }
        return $filteredTesters;
    }

    private function readDataFromXLSX($inputs)
    {
        $spreadsheet = IOFactory::load($inputs['file']->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]);
        $testersData = [];
        foreach ($rows as $row) {
            $testerKey = $row['C'] . ' ' . $row['D'];

            if (!isset($testersData[$testerKey])) {
                $testersData[$testerKey] = [
                    'testerData' => [
                        'testName' => $row['A'],
                        'name' => $row['C'],
                        'lastName' => $row['D'],
                        'email' => $row['E'],
                    ],
                    'steps' => [],
                ];
            }
            $testersData[$testerKey]['steps'][] = [
                'stepNumber' => $row['B'],
                'questionType' => $row['F'],
                'question' => $row['G'],
                'testerResponse' => $row['H'],
                'comment' => $row['I'],
                'lowerBoundLabel' => $row['J'],
                'upperBoundLabel' => $row['K'],
                'min' => $row['L'],
                'max' => $row['M'],
            ];
        }
        return array_values($testersData);
    }

    private function SplitEntityAdventages(String $phraseToRemove, String $text):string
    {
        return str_replace($phraseToRemove, '', $text);
    }

    private function SplitString($data){
        $recommendations = preg_split('/[1-5]\./', $data);
        array_shift($recommendations);

        return [
            "1" => isset($recommendations[0]) ? trim(preg_replace('/\*\*/', '', $recommendations[0])) : null,
            "2" => isset($recommendations[1]) ? trim(preg_replace('/\*\*/', '', $recommendations[1])) : null,
            "3" => isset($recommendations[2]) ? trim(preg_replace('/\*\*/', '', $recommendations[2])) : null,
            "4" => isset($recommendations[3]) ? trim(preg_replace('/\*\*/', '', $recommendations[3])) : null,
            "5" => isset($recommendations[4]) ? trim(preg_replace('/\*\*/', '', $recommendations[4])) : null,
        ];
    }
    private function extractKeyValuePairs($text) {
        $data = [];
        preg_match_all('/\*\*(.*?)\*\*\s*(.*?)(?=\n\d\.|\z)/s', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = trim($match[1]);
            $value = trim($match[2]);
            $data[$key] = $value;
        }
        return $data;
    }

    function isValidUrl(string|null $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function getOpenAITargetedRecommendations(array $sortedData):array
    {
        foreach ($sortedData as $key=>$stepData) {
            $data = [
                'bilan_redaction' => $this->translator->trans('targeted_recommendations.writing_assessment', ['ENTITY' => json_encode($stepData)]),
                'analyse_score' => $this->translator->trans('targeted_recommendations.analyse_score', ['ENTITY' => json_encode($stepData)])
            ];
            $bilan_redaction = $this->openAIServiceProvider->getChatGPTAnalyses($data["bilan_redaction"]);
            $analyse_score = $this->openAIServiceProvider->getChatGPTAnalyses($data["analyse_score"]);
            $feedback = [];
            foreach ($stepData['answers'] as $stepValue) {
                $feedback_prompt = $this->translator->trans('targeted_recommendations.most_remarquable_feedback', ['ENTITY' => json_encode($stepValue)]);
                $chatgptResponse = $this->openAIServiceProvider->getChatGPTAnalyses($feedback_prompt);
                $feedback[]=[
                    1 => $chatgptResponse['choices'][0]['message']['content'],
                    2 => $stepValue['answer'].' '."({$this->transformName($stepValue['client_name'])})"
                ];
            }
            try {
                $mostRemaquableFeedbackNumber = $this->translator->trans('targeted_recommendations.which_most_remarquable', ['ENTITY' => json_encode($feedback)]);
                $chatgptRes = $this->openAIServiceProvider->getChatGPTAnalyses($mostRemaquableFeedbackNumber);
                $keysToKeep = explode(', ', $chatgptRes['choices'][0]['message']['content']);
                $finallFeedback = array_intersect_key($feedback, array_flip($keysToKeep));

            } catch (\Exception $e) {
                $finallFeedback = $feedback;
            }
            $sortedData[$key]['bilan_redaction'] = $bilan_redaction['choices'][0]['message']['content'];
            $sortedData[$key]['analyse_score'] = $analyse_score['choices'][0]['message']['content'];
            $sortedData[$key]['most_remarquable_feedback']  = array_values($finallFeedback);
        }

        return $sortedData;
    }
    private function transformName(string $name): string
    {
        $parts = explode(' ', $name);
        if (count($parts) < 2) {
            return $name;
        }
        $firstName = $parts[0];
        $lastInitial = strtoupper($parts[1][0]);

        return sprintf("%s .%s", $firstName, $lastInitial);
    }
}
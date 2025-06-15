<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Entity\Answer;
use App\Entity\FaceShot;
use App\Entity\Salience;
use App\Entity\Sentence;
use App\Entity\Test;
use App\Exception\BadResponseExternalWebserviceException;
use Doctrine\ORM\EntityManagerInterface;
use Aws\Rekognition\RekognitionClient;
use Aws\Exception\AwsException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AllowDynamicProperties] class VideoAnalyze {
    private $videoFfmpeg;
    private $googleApi;
    private $containerBag;
    private $entityManager;
    private $googleApisLogger;
    private $responseService;
    private $serializer;

    public function __construct(SerializerInterface $serializer,ResponseService $responseService,LoggerInterface $googleApisLogger,VideoFfmpeg $videoFfmpeg, GoogleApi $googleApi, ContainerBagInterface $containerBag, EntityManagerInterface $entityManager)
    {
        $this->videoFfmpeg = $videoFfmpeg;
        $this->googleApi = $googleApi;
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
        $this->googleApisLogger = $googleApisLogger;
        $this->responseService = $responseService;
        $this->serializer = $serializer;
        $this->client = new RekognitionClient([
            'region'    => $this->containerBag->get('AWS_REGION'),
            'version'   => $this->containerBag->get('AWS_VERSION'),
            'credentials' => [
                'key'    => $this->containerBag->get('AWS_ACCESS_KEY'),
                'secret' => $this->containerBag->get('AWS_SECRET_KEY'),
            ]
        ]);
    }

    public function analyze(Test $test)
    {
        if (!$test->getIsAnalyzed())
        {
            try {
                $response = [];
                foreach ($test->getAnswers() as $key => $answer)
                {
                    $this->googleApisLogger->info('_______________________________________________________________________________');
                    $this->googleApisLogger->info('STARTING STEP ANALYZE : '. $answer->getClientTester()->getName() . ' ' .$answer->getClientTester()->getName() .' Type: '.$answer->getStep()->getType() .' STEP ID : '.$answer->getStep()->getId() .' Number : '.($key+1));
                    $this->AWSFaceShotsAnalyze($answer);
                    switch ($answer->getStep()->getType()) {
                        case 'close':
                        case 'open':
                            $this->analyseOpenAndClosedSteps($answer);
                            break;
                        case 'scale':
                            $this->analyseScaleStep($answer);
                            break;
                    }
                    if(!is_numeric($answer->getAnswer()) && $answer->getAnswer())
                    {
                            $textToAnalyze =  $answer->getAnswer();
                    }else
                    {
                        $textToAnalyze = $answer->getComment();
                    }
                    $this->googleApisLogger->info('Google natural Language Text Before Analyze : '.$textToAnalyze);
                    $this->googleApisLogger->info('First step analyze : Type: '.$answer->getStep()->getType(). ' Text : '.$textToAnalyze);
                    if (!is_numeric($textToAnalyze) && $textToAnalyze) {
                        $response = $this->googleApi->annotation($textToAnalyze);
                        $this->googleApisLogger->info('Google natural Language response : '. json_encode($response, true));
                        if (!isset($response['documentSentiment']['magnitude']) || !isset($response['documentSentiment']['score'])) {
                            $this->googleApisLogger->info('RESPONSE ERROR : Google natural Language : '.json_encode($response, true));
                        }
                        $answer->setMagnitudeVideo($this->truncateToTwoDecimals($response['documentSentiment']['magnitude']));
                        $answer->setScoreVideo($this->truncateToTwoDecimals($response['documentSentiment']['score']));
                        $this->annotateSentencesOfText($response,$answer);
                        $this->annotateSaliencesOfText($response,$answer);

                    }else
                    {
                        $this->googleApisLogger->info('TEXT ERROR : Google natural Language : '.$textToAnalyze);
                    }
                    $this->googleApisLogger->info('END STEP ANALYZE : Type: '.$answer->getStep()->getType() .' STEP ID : '.$answer->getStep()->getId() .' Number : '.($key+1));
                    $this->googleApisLogger->info('_______________________________________________________________________________');
                }
                $test->setIsAnalyzed(true);
                $test->getScenario()->setIsTested($test->getScenario()->getIsTested() + 1);
                $this->entityManager->flush();
                $this->googleApisLogger->info('Final Response : '.'Google natural Language response : '. json_encode($response, true) ." Test analyze successfully :". json_encode($test, true));
                $this->googleApisLogger->info('_______________________________________________________________________________');


                return $this->responseService->getResponseToClient($response);
            }catch (\Exception $exception)
            {
                $this->googleApisLogger->info('Server Error : '. json_encode($exception->getMessage(), true));
                return $exception->getMessage();
            }
        }else
        {
            $this->googleApisLogger->info('THIS TEST IS ALREADY ANALYZED : '. $this->serializer->serialize($test, 'json', ['groups' => 'get_test']));
            return $this->responseService->getResponseToClient(null,200,'test.already_tested');
        }

    }

    private function annotateSentencesOfText($response,$answer)
    {
        foreach ($response['sentences'] as $sentence)
        {
            $sen = new Sentence();
            $sen->setContent($sentence['text']['content'])
                ->setMagnitude($this->truncateToTwoDecimals($sentence['sentiment']['magnitude']))
                ->setScore($this->truncateToTwoDecimals($sentence['sentiment']['score']));
            $this->entityManager->persist($sen);
            $answer->addSentence($sen);
        }
    }
    private function annotateSaliencesOfText($response,$answer)
    {
            $sal = new Salience();
            $sal->setWord($response['saliences']['name'])
                ->setType($response['saliences']['type']);
            if (gettype($response['saliences']['name']) == "string") {
                $sal->setSalience(0);
            } else {
                $sal->setSalience($response['saliences']['name']);
            }
            $this->entityManager->persist($sal);
            $answer->addSalience($sal);
    }

    private function analyseOpenAndClosedSteps(Answer $answer): void
    {
        if (!is_numeric($answer->getAnswer()))
        {
            $this->googleApisLogger->info('_____________________________________OPEN AND CLOSED STEPS START__________________________________________');
            $this->googleApisLogger->info('STARTING STEP ANALYZE : Type: '.$answer->getStep()->getType() .' STEP ID : '.$answer->getStep()->getId());
            $response = $this->googleApi->annotation($answer->getAnswer());
            $response2 = $this->googleApi->annotation($answer->getAnswer().$answer->getComment());
            $this->googleApisLogger->info('Response :'. json_encode($response, true) .'Type: '.$answer->getStep()->getType() .' STEP ID : '.$answer->getStep()->getId());
            $answer->setMagnitude($this->truncateToTwoDecimals($response['documentSentiment']['magnitude']));
            $answer->setScore($this->truncateToTwoDecimals($response['documentSentiment']['score']));
            $answer->setMagnitudeComments($this->truncateToTwoDecimals($response2['documentSentiment']['magnitude']));
            $answer->setScoreComments($this->truncateToTwoDecimals($response2['documentSentiment']['score']));
            $this->googleApisLogger->info('_____________________________________OPEN AND CLOSED STEPS END__________________________________________');
        }else
        {
            $answer->setMagnitude(0);
            $answer->setScore(0);
            $answer->setMagnitudeComments(0);
            $answer->setScoreComments(0);
        }
    }

    private function analyseScaleStep(Answer $answer): void
    {
        $this->googleApisLogger->info('Logs Scale client response starting ... : Answer : ' . $answer->getAnswer());
        if ($answer->getAnswer()) {
            try {

                $MinScale = $answer->getStep()->getQuestionChoices()->getMinScale()??1;
                $MaxScale = $answer->getStep()->getQuestionChoices()->getMaxScale();
                $BorneInf = $answer->getStep()->getQuestionChoices()->getBorneInf();
                $BorneSup = $answer->getStep()->getQuestionChoices()->getBorneSup();
                if ($BorneInf === '' || $BorneInf === null) {
                    $BorneInf = "Pas interessant";
                }

                if ($BorneSup === '' || $BorneSup === null) {
                    $BorneSup = "interessant";
                }
                $this->googleApisLogger->info('Logs Scale Data : Answer :' . $answer->getAnswer().' MinScale :'. $MinScale.' MaxScale: '.$MaxScale.' BorneInf: '.$BorneInf.' BorneSup: '.$BorneSup);
                $responseBorneInf = $this->googleApi->annotation($BorneInf);
                $this->googleApisLogger->info('Logs Google Natural Language BorneInf Response : '.json_encode($responseBorneInf,true));
                $responseBorneSup = $this->googleApi->annotation($BorneSup);
                $this->googleApisLogger->info('Logs Google Natural Language BorneSup Response : '.json_encode($responseBorneSup,true));
                $response  = $this->calculateAnswerScoreMagnitudeScale($answer->getAnswer(),$responseBorneInf,$responseBorneSup,$MaxScale);
                $this->googleApisLogger->info('Logs Final Response For Answer : ' . $answer->getAnswer().' Is : '. json_encode($response,true) );

                $responseBorneInf2 = $this->googleApi->annotation($BorneInf.$answer->getComment());
                $this->googleApisLogger->info('Logs Google Natural Language BorneInf Response 2 : '.json_encode($responseBorneInf2,true));
                $responseBorneSup2 = $this->googleApi->annotation($BorneSup.$answer->getComment());
                $this->googleApisLogger->info('Logs Google Natural Language BorneSup Response 2 : '.json_encode($responseBorneSup2,true));
                $response2  = $this->calculateAnswerScoreMagnitudeScale($answer->getAnswer(),$responseBorneInf2,$responseBorneSup2,$MaxScale);
                $this->googleApisLogger->info('Logs Final Response For Answer 2 : ' . $answer->getAnswer().' Is : '. json_encode($response,true) );

                $answer->setScore($this->truncateToTwoDecimals($response["score"]));
                $answer->setMagnitude($this->truncateToTwoDecimals($response["magnitude"]));
                $answer->setMagnitudeComments($this->truncateToTwoDecimals($response2['documentSentiment']['magnitude']));
                $answer->setScoreComments($this->truncateToTwoDecimals($response2['documentSentiment']['score']));

            }catch (\Exception $exception)
            {
                $this->googleApisLogger->info('Logs Scale Data ERROR... : Answer : ' . $answer->getAnswer().' Error : '.$exception->getMessage());
                $answer->setScore(0);
                $answer->setMagnitude(0);
            }
        } else {
            $answer->setScore(0);
            $answer->setMagnitude(0);
        }

    }

    private function calculateAnswerScoreMagnitudeScale($answer,$responseBorneInf,$responseBorneSup,$MaxScale)
    {
        $finaleScore = 0;
        $finalMagnitude = 0;
        try {
            $BorneInfScore = $responseBorneInf['documentSentiment']['score'];
            $BorneSupScore = $responseBorneSup['documentSentiment']['score'];
            $BorneInfMagnitude = $responseBorneInf['documentSentiment']['magnitude'];
            $BorneSupMagnitude = $responseBorneSup['documentSentiment']['magnitude'];
            $diffScore = $BorneSupScore - $BorneInfScore;
            $diffMagnitude = $BorneSupMagnitude - $BorneInfMagnitude;
            $PasScore = $diffScore / $MaxScale;
            $PasMagnitude = $diffMagnitude / $MaxScale;
            $finaleScore = $BorneInfScore + ($PasScore*($answer-1));
            $finalMagnitude = $BorneInfMagnitude + ($PasMagnitude*($answer-1));

            $finaleRes = [
                "score" => $finaleScore,
                "magnitude" => $finalMagnitude
            ];

            return $finaleRes;

        }catch (\Exception $exception)
        {
            $this->googleApisLogger->info('Logs Scale client response Fatal ERROR ... : '. $exception->getMessage());
            return $finaleRes = [
                "score" => $finaleScore,
                "magnitude" => $finalMagnitude
            ];
        }
    }

    public function AWSFaceShotsAnalyze($answer){
        try {
            $EmotionsLists = array();
            $faceshotsPics = $this->entityManager->getRepository(FaceShot::class)->findBy(["answer" => $answer]);
            if ($faceshotsPics) {
                foreach ($faceshotsPics as $faceshotsPic) {
                    try {
                        $imageData = file_get_contents($faceshotsPic->getImage());
                        $awsResponse = $this->client->detectFaces([
                            'Image' => [
                                'Bytes' => $imageData,
                            ],
                            'Attributes' => ['EMOTIONS'],
                        ]);
                        $awsEmotions = $this->getEmotionByType($awsResponse['FaceDetails'][0]['Emotions']);
                        $faceshotsPic->setCalm($awsEmotions["CALM"]??0);
                        $faceshotsPic->setAngry($awsEmotions["ANGRY"]??0);
                        $faceshotsPic->setSad($awsEmotions["SAD"]??0);
                        $faceshotsPic->setConfused($awsEmotions["CONFUSED"]??0);
                        $faceshotsPic->setDisgusted($awsEmotions["DISGUSTED"]??0);
                        $faceshotsPic->setSurprised($awsEmotions["SURPRISED"]??0);
                        $faceshotsPic->setHappy($awsEmotions["HAPPY"]??0);
                        $faceshotsPic->setFear($awsEmotions["FEAR"]??0);
                        $this->entityManager->persist($faceshotsPic);
                        $this->entityManager->flush();
                        array_push($EmotionsLists,$awsEmotions);
                    }catch (AwsException $exception){
                        $faceshotsPic->setCalm(-1);
                        $faceshotsPic->setAngry(-1);
                        $faceshotsPic->setSad(-1);
                        $faceshotsPic->setConfused(-1);
                        $faceshotsPic->setDisgusted(-1);
                        $faceshotsPic->setSurprised(-1);
                        $faceshotsPic->setHappy(-1);
                        $faceshotsPic->setFear(-1);
                        $this->entityManager->persist($faceshotsPic);
                        $this->entityManager->flush();
                        $this->googleApisLogger->info('Logs AWS Data ERROR... : Answer : ' . $answer->getAnswer().' Error : '.$exception->getMessage());
                    }

                }
                $emotionsArray = $this->calculateAVGEmotions($EmotionsLists);
                $answer->setCalm($emotionsArray["CALM"]??0);
                $answer->setAngry($emotionsArray["ANGRY"]??0);
                $answer->setSad($emotionsArray["SAD"]??0);
                $answer->setConfused($emotionsArray["CONFUSED"]??0);
                $answer->setDisgusted($emotionsArray["DISGUSTED"]??0);
                $answer->setSurprised($emotionsArray["SURPRISED"]??0);
                $answer->setHappy($emotionsArray["HAPPY"]??0);
                $answer->setFear($emotionsArray["FEAR"]??0);
                $this->entityManager->persist($answer);
                $this->entityManager->flush();

            } else {
                return false;
            }
        } catch (AwsException $exception) {
            $this->googleApisLogger->info('Logs AWS Data ERROR... : Answer : ' . $answer->getAnswer().' Error : '.$exception->getMessage());
        }
        return true;
    }

    public function getEmotionByType($awsEmotions){
        $emotionConfidences = [];
        foreach ($awsEmotions as $emotion) {
            $emotionType = $emotion['Type'];
            $emotionConfidence = $emotion['Confidence'];
            $emotionConfidences[$emotionType] = $emotionConfidence;
        }
        return $emotionConfidences;
    }
    public function calculateAVGEmotions($EmotionsLists) {
        $emotionSums = [];
        $emotionCounts = [];
        $emotionAverages = [];
        $this->collectEmotions($EmotionsLists, $emotionSums, $emotionCounts);
        foreach ($emotionSums as $type => $sum) {
            $emotionAverages[$type] = $sum / $emotionCounts[$type];
        }
        return $emotionAverages;
    }

    private function collectEmotions($emotionSets, &$emotionSums, &$emotionCounts) {
        $allEmotions = [];
        foreach ($emotionSets as $emotionSet) {
            $allEmotions[] = $emotionSet;
        }
        $emotionSums = array_reduce($allEmotions, function ($carry, $item) {
            foreach ($item as $emotionType => $confidence) {
                $carry[$emotionType] = ($carry[$emotionType] ?? 0) + $confidence;
            }
            return $carry;
        }, []);

        $emotionCounts = array_reduce($allEmotions, function ($carry, $item) {
            foreach ($item as $emotionType => $confidence) {
                $carry[$emotionType] = ($carry[$emotionType] ?? 0) + 1;
            }
            return $carry;
        }, []);
    }
    private function truncateToTwoDecimals($number)
    {
        return substr($number, 0, strpos($number, '.') + 3);
    }
}

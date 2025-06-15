<?php

namespace App\Service;

use Google\Cloud\Speech\V1p1beta1\RecognitionAudio;
use Google\Cloud\Speech\V1p1beta1\RecognitionConfig;
use Google\Cloud\Speech\V1p1beta1\SpeechClient;
use Google\Cloud\Vision\V1\AnnotateImageResponse;
use Google\Cloud\Vision\V1\FaceAnnotation;
use Google\Cloud\Vision\V1\Client;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Language\V2\AnalyzeSentimentRequest;
use Google\Cloud\Language\V2\AnalyzeEntitiesRequest;
use Google\Cloud\Language\V2\Client\LanguageServiceClient;
use Google\Cloud\Language\V2\Document;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class GoogleApi
{
    private $ERROR_GNL = '{
                  "documentSentiment": {
                    "magnitude": 0,
                    "score": 0
                  },
                  "sentences": [
                    {
                      "text": {
                        "content": "ERROR",
                        "beginOffset": -1
                      },
                      "sentiment": {
                        "magnitude": 0,
                        "score": 0
                      }
                    }
                  ],
                  "saliences": {
                    "name": "Unknown",
                    "type": 8
                  },
                  "syntax": null
                }';

    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function annotation($textToAnalyze)
    {
        try {
            $path = $this->parameterBag->get('GOOGLE_APPLICATION_CREDENTIALS_PATH');
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$path");
            $languageServiceClient = new LanguageServiceClient();
            $document = (new Document())
                ->setContent($textToAnalyze)
                ->setType(Document\Type::PLAIN_TEXT);
            $syntax = (new AnalyzeSentimentRequest())
                ->setDocument($document);
            $sentimentsRequest = (new AnalyzeSentimentRequest())
                ->setDocument($document);
            $salienceRequest = (new AnalyzeEntitiesRequest())
                ->setDocument($document);

            $SentimentResponse = $languageServiceClient->analyzeSentiment($sentimentsRequest);
            $SalienceResponse = $languageServiceClient->analyzeEntities($salienceRequest);

            $salience = match ($SalienceResponse->getEntities()->getType()) {
                1 => 'PERSON',
                2 => 'LOCATION',
                3 => 'ORGANIZATION',
                4 => 'EVENT',
                5 => 'WORK_OF_ART',
                6 => 'CONSUMER_GOOD',
                7 => 'OTHER',
                9 => 'PHONE_NUMBER',
                10 => 'ADDRESS',
                11 => 'DATE',
                12 => 'NUMBER',
                13 => 'PRICE',
                default => 'Unknown',
            };
            $saliences = [
                'name' => $salience,
                "type" => $SalienceResponse->getEntities()->getType()?? 8
            ];
            $responseToArray = json_decode($SentimentResponse->serializeToJsonString(), true);
            return [
                'documentSentiment' => $responseToArray["documentSentiment"],
                'sentences' => $responseToArray["sentences"],
                'saliences' => $saliences,
                'syntax' => null
            ];

        }catch (\Exception $exception)
        {
            $responseToArray = json_decode($this->ERROR_GNL, true);
            $saliences = [
                'name' => "Unknown",
                "type" => 8
            ];
            return [
                'documentSentiment' => $responseToArray["documentSentiment"],
                'sentences' => $responseToArray["sentences"],
                'saliences' => $saliences,
                'syntax' => $exception->getMessage()
            ];
        }
    }

    public function speechToText($audioFile)
    {
        $projectId = 'deft-province-297409';
        $client = new SpeechClient(['projectId' => $projectId]);
        $audioResource = file_get_contents($audioFile);
        $encoding = RecognitionConfig\AudioEncoding::MP3;
        $sampleRateHertz = 48000;
        $languageCode = 'fr-FR';
        $config = new RecognitionConfig();
        $config->setEncoding($encoding);
        $config->setSampleRateHertz($sampleRateHertz);
        $config->setLanguageCode($languageCode);
        $config->setEnableAutomaticPunctuation(true);
        $audio = new RecognitionAudio();
        $audio->setContent($audioResource);
        $response = $client->recognize($config, $audio);
        $text = "";
        foreach ($response->getResults() as $result) {
            $alt = $result->getAlternatives();
            $mostlike = $alt[0];
            $text .= ". " . $mostlike->getTranscript();
        }
        $text = substr($text, 2);

        return $text;
    }

    public function faceRecognition($image)
    {
        $client = new ImageAnnotatorClient();
        $annotation = $client->annotateImage(
            fopen($image, 'r'),
            [Type::FACE_DETECTION]
        );
        $ann = $annotation->getFaceAnnotations();
        $result = [];
        foreach ($ann as $faceAnnotation) {
            $result["anger"] = $faceAnnotation->getAngerLikelihood();
            $result["surprise"] = $faceAnnotation->getSurpriseLikelihood();
            $result["joy"] = $faceAnnotation->getJoyLikelihood();
            $result["confidence"] = $faceAnnotation->getDetectionConfidence();

        }
        return $result;
    }

}

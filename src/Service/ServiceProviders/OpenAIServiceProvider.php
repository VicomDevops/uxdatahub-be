<?php

namespace App\Service\ServiceProviders;

use App\Utils\ConfigManager;
use App\Utils\HttpClientHelper;
use App\Utils\ParamsHelper;
use Orhanerday\OpenAi\OpenAi as chatGPT;
use phpDocumentor\Reflection\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class OpenAIServiceProvider
{
    private $paramsHelper;
    private $configManager;
    private $httpClientHelper;
    private $client;


    public function __construct(ParamsHelper $paramsHelper, ConfigManager $configManager, HttpClientHelper $httpClientHelper,HttpClientInterface $client)
    {
        $this->paramsHelper = $paramsHelper;
        $this->configManager = $configManager;
        $this->httpClientHelper = $httpClientHelper;
        $this->client = $client;
    }

    public function getChatGPTAnalyses($messages): JsonResponse|bool|string|array
    {
        $configWs = $this->configManager->getConfigWs('openAI','openAI_chatgpt');
        $payload = [
            'model' => $configWs['model'],
            'messages' => [
                [
                    "role" => $configWs['role'],
                    "content" => $messages
                ]
            ],
            'temperature' => $configWs['temperature'],
            'max_tokens' => $configWs['max_tokens'],
            'frequency_penalty' => $configWs['frequency_penalty'],
            'presence_penalty' => $configWs['presence_penalty'],
        ];
        $files = null;
        $headers = [];
        $options = null;

        return $this->httpClientHelper->callHttpClientAI($configWs, $payload, $files, $headers, $options);
    }
    public function createAIChatGPTImagesProvider($messages): JsonResponse|bool|string|array
    {
        $configWs = $this->configManager->getConfigWs('openAI','openAI_chatgpt');
        $payload = [
            "prompt" => $messages,
            "n" => 1,
            "size" => '1024x1024',
            "response_format" => "b64_json"
        ];
        $files = null;
        $headers = [];
        $options = null;

        return $this->httpClientHelper->callHttpClientAIImages($configWs, $payload, $files, $headers, $options);
    }
}
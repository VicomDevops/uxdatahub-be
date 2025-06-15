<?php

namespace App\Utils;

use App\Exception\BadResponseExternalWebserviceException;
use Orhanerday\OpenAi\OpenAi;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class HttpClientHelper
{
    const ACCEPT_STATUS_CODE = array(400, 404);
    private ParamsHelper $paramsHelper;
    private const MAX_RETRIES = 5;
    private const INITIAL_WAIT_TIME = 1; // in seconds
    private const ACCEPT_STATUS_CODES = [200, 201, 202, 203, 204];

    public function __construct(ParamsHelper $paramsHelper)
    {
        $this->paramsHelper = $paramsHelper;
    }

    /**
     * @param string $method
     * @param array $configClient
     * @param array $payload
     * @param array $headers
     * @return bool|string
     * @throws
     */

    public function callHttpClientAI(array $configWs = array(), array $payload = array(), UploadedFile|null $file = null,array $headers = array(), array|string $options = null)
    {
        try{
            $response = $this->callAPIsAI($configWs, $payload, $headers, $file, $options);
        }catch (\Exception $exception)
        {
            return new JsonResponse([
                'code' => http_response_code(),
                'message' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    public function callHttpClientAIImages(array $configWs = array(), array $payload = array(), UploadedFile|null $file = null,array $headers = array(), array|string $options = null)
    {
        try{
            $response = $this->createAIChatGPTImages($configWs, $payload, $headers, $file, $options);
        }catch (\Exception $exception)
        {
            return new JsonResponse([
                'code' => http_response_code(),
                'message' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * @throws BadResponseExternalWebserviceException
     */
    private function callAPIsAI(array $configWs = array(), array $payload = array(), array $headers = array(), UploadedFile|null $file = null, array|string $options = null)
    {
        $requestMessageClient = $this->getMessageToLog($this->getLogsURLFromConfig($configWs), $payload, $headers);
        $this->paramsHelper->flushString($requestMessageClient, array('domain' => '[Call Request] : ', 'method' => 'info'));
        try {
            $open_ai = new OpenAi($configWs['token']);
            $chatGPTresponse = $open_ai->chat($payload);
        }catch (\Exception $exception) {
            $response = '[Call Response] : ' . $exception->getCode() . ' -- ' . $exception->getMessage();
            $this->paramsHelper->flushString((string)$response, array('domain' => '[Call Response] : ', 'method' => 'critical'));
            if (in_array($exception->getCode(), self::ACCEPT_STATUS_CODE)) {
                return $exception->getMessage();
            }
            throw new BadResponseExternalWebserviceException('general.500');
        }
        $this->paramsHelper->flushString($chatGPTresponse, array('domain' => '[Call Response] : ', 'method' => 'info'));

        return json_decode($chatGPTresponse, true);
    }

    private function createAIChatGPTImages(array $configWs = array(), array $payload = array(), array $headers = array(), UploadedFile|null $file = null, array|string $options = null)
    {
        $requestMessageClient = $this->getMessageToLog($this->getLogsURLFromConfig($configWs), $payload, $headers);
        $this->paramsHelper->flushString($requestMessageClient, array('domain' => '[Call Request] : ', 'method' => 'info'));
        try {
            $open_ai = new OpenAi($configWs['token']);
            $chatGPTresponse = $open_ai->image($payload);
        }catch (\Exception $exception) {
            $response = '[Call Response] : ' . $exception->getCode() . ' -- ' . $exception->getMessage();
            $this->paramsHelper->flushString((string)$response, array('domain' => '[Call Response] : ', 'method' => 'critical'));
            if (in_array($exception->getCode(), self::ACCEPT_STATUS_CODE)) {
                return $exception->getMessage();
            }
            throw new BadResponseExternalWebserviceException('general.500');
        }
        $this->paramsHelper->flushString($chatGPTresponse, array('domain' => '[Call Response] : ', 'method' => 'info'));

        return json_decode($chatGPTresponse, true);
    }

    private function getLogsURLFromConfig($configClient)
    {
        return isset($configClient['url_logs']) && !empty($configClient['url_logs']) ? $configClient['url_logs']: $configClient['url'];
    }

    private function getMessageToLog($config, $payload, $headers = array())
    {
        return $config . ' -- params : ' . json_encode($this->filterParamsToFlush($payload)) . ' headers : ' . json_encode($headers);
    }

    private function filterParamsToFlush(array $fields): array
    {
        $dataFieldsToFlush = array();
        foreach ($fields as $fieldKey => $fieldData) {
            $dataFieldsToFlush[$fieldKey] = $this->paramsHelper->filterParamsToFlush([$fieldData]);
        }
        return $dataFieldsToFlush;
    }
}
<?php

namespace App\Service;

use App\Representation\ResponseToClient;
use App\Utils\ParamsHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ResponseService
{
    const KEY_INVALID_PARAMS    = 'general.params';
    const KEY_FORBIDDEN         = 'general.forbidden';

    private $translator;

    private $paramsHelper;

    public function __construct(TranslatorInterface $translator, ParamsHelper $paramsHelper)
    {
        $this->translator = $translator;
        $this->paramsHelper = $paramsHelper;
    }

    public function getResponseToClient($response = null, int $code = 200, string $messageKey = 'general.success', string $domain = 'messages', array $paramsMessage = array()): JsonResponse
    {
        $response = new ResponseToClient($response, $code, $this->getMessage($messageKey, $domain, $paramsMessage));
        $this->paramsHelper->flushResponseToClient($response);
        $serializer = new Serializer([new ObjectNormalizer()]);
        return new JsonResponse($serializer->normalize($response));
    }

    public function getResponseException(int $code, string $messageKey, string $domain = 'messages', array $paramsMessage = array()): JsonResponse
    {
        $response = new JsonResponse(
            array(
                'header' => array('code' => $code, 'message' => $this->getMessage($messageKey, $domain, $paramsMessage)),
                'response' => null
            ),
            Response::HTTP_OK
        );

        $response->headers->set('X-Status-Code', 200);

        $this->paramsHelper->flushJsonResponse($response);
        return $response;
    }

    /**
     * @param string $messageKey
     * @param string $domain
     * @param array $paramsMessage
     * @return string
     */
    private function getMessage(string $messageKey, string $domain = 'messages', array $paramsMessage = array()): string
    {
        $message = $this->translator->trans($messageKey, $paramsMessage, $domain);

        if ($message !== $messageKey) {
            return $message;
        }

        return $this->translator->trans('general.500', $paramsMessage, $domain);
    }
}
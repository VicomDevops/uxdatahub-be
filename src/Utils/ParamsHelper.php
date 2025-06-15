<?php

namespace App\Utils;

use App\Representation\ResponseToClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

class ParamsHelper
{
    const INPUT_NOT_FLUSHABLE = array('password');

    private array $inputs = array();

    private ?string $login;

    private ?string $versionMobile;

    private string $locale;

    private ?string $ip;

    private string $pip;

    private ?string $os;

    private string $method;

    private ?string $routeName;

    private SerializerInterface $serializer;

    private $logger;

    private ?string $tokenUser;

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $request = $requestStack->getMainRequest();

        if ($request instanceof Request) {
            $this->ip = $request->getClientIp();
            $this->pip = getmygid();
            $this->routeName = $request->get('_route');
            $this->method = $request->getMethod();
            $this->os = $request->headers->get('os');
            $this->versionMobile = $this->getVersionFormAcceptHeader($request->headers->get('Accept'));
            $this->locale = $request->getLocale();
            $authorizationHeader = $request->headers->get('Authorization');
            if (!empty($authorizationHeader) && strpos($authorizationHeader, 'Bearer ') === 0) {
                $this->tokenUser = substr($authorizationHeader, 7);
            }
            $user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
            if ($user instanceof UserInterface) {
                $this->login = $user->getUsername();
            }
        } else {
            $this->ip = 'unknown';
            $this->pip = getmygid();
            $this->routeName = 'unknown';
            $this->method = 'unknown';
            $this->os = 'unknown';
            $this->versionMobile = 'unknown';
            $this->locale = 'unknown';
            $this->tokenUser = 'unknown';
            $this->login = 'unknown';
        }

        $this->serializer = $serializer;
        $this->logger = $logger;

    }

    /**
     * @return array
     */
    public function getInputs(): ?array
    {
        return $this->inputs;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getInputByName($name)
    {
        return isset($this->inputs[$name]) ? $this->inputs[$name] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param boolean|false $isForced
     * @return array|null
     */
    public function addInputs($key, $value, $isForced = false): ?array
    {
        if (!key_exists($key, $this->inputs) || $isForced) {
            $this->inputs[$key] = $value;
        }

        return  $this->inputs;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param array $inputs
     * @return ParamsHelper
     */
    public function setInputs(?array $inputs): ParamsHelper
    {
        $this->inputs = $inputs;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersionMobile(): string
    {
        return $this->versionMobile;
    }

    /**
     * @param string $versionMobile
     * @return ParamsHelper
     */
    public function setVersionMobile(string $versionMobile): ParamsHelper
    {
        $this->versionMobile = $versionMobile;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return ParamsHelper
     */
    public function setLocale(string $locale): ParamsHelper
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return ParamsHelper
     */
    public function setIp(string $ip): ParamsHelper
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @param string $os
     * @return ParamsHelper
     */
    public function setOs(string $os): ParamsHelper
    {
        $this->os = $os;
        return $this;
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param SerializerInterface $serializer
     * @return ParamsHelper
     */
    public function setSerializer(SerializerInterface $serializer): ParamsHelper
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return bool
     */
    public function flushInputWithLogger()
    {
        if ($this->logger instanceof LoggerInterface) {
            if (!empty($this->inputs)) {
                $this->logger->info($this->loggerIdentifier() . 'RequestFromClient : ' .$this->serializer->serialize($this->filterParamsToFlush($this->inputs), 'json'));
            }else{
                $this->logger->info($this->loggerIdentifier() . 'RequestFromClient : No inputs to flush');
            }

            return true;
        }

        return false;
    }

    /**
     * @param ResponseToClient $responseToClient
     * @return bool
     */
    public function flushResponseToClient(ResponseToClient $responseToClient): bool
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info($this->loggerIdentifier() . 'ResponseToClient : ' . $this->serializer->serialize($responseToClient, 'json'));
            return true;
        }

        return false;
    }

    /**
     * @param JsonResponse $response
     * @return bool
     */
    public function flushJsonResponse(JsonResponse $response): bool
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info($this->loggerIdentifier() . 'ResponseToClient : ' . $response->getContent());
            return true;
        }

        return false;
    }

    public function flushString(string $message, array $context)
    {
        if (!$this->logger instanceof LoggerInterface) {
            return false;
        }

        switch ($context['method'])
        {
            case 'info':
                $this->logger->info($this->loggerIdentifier() . $context['domain'] . ' -- ' .$message);
                break;
            case 'critical':
                $this->logger->critical($this->loggerIdentifier() . $context['domain'] . ' -- ' .$message);
                break;
            case 'warning':
                $this->logger->warning($this->loggerIdentifier() . $context['domain'] . ' -- ' .$message);
                break;
            default :
                break;
        }

        return true;
    }

    /**
     * @param string $target
     * @param bool $fromInput
     */
    public function updateLoginIdentifier($target, $fromInput = true)
    {
        if ($fromInput && isset($this->inputs[$target])) {
            $this->login = $this->inputs[$target];
            return;
        }

        $this->login = $target;
    }

    public function getTokenUser(): ?string
    {
        return $this->tokenUser;
    }

    /**
     * @return string
     */
    private function loggerIdentifier()
    {
        return "[$this->ip][$this->pip][$this->routeName][$this->method][$this->os][$this->versionMobile][$this->locale]";
    }

    /**
     * @param string $acceptHeader
     * @return string
     */
    private function getVersionFormAcceptHeader(string $acceptHeader): ?string
    {
        $data = explode(';', $acceptHeader);
        if (count($data) == 2) {
            $dataVersion = explode('=', $data[1]);
            return (count($dataVersion) == 2 && ($dataVersion[0] == 'version' || $dataVersion['0'] = 'v')) ? $dataVersion[1] : '0.0';

        }

        return '0.0';
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function filterParamsToFlush(array $inputs)
    {
        return array_filter($inputs, function ($index) {
            return in_array($index, self::INPUT_NOT_FLUSHABLE) ? false : true;
        }, ARRAY_FILTER_USE_KEY);
    }
}

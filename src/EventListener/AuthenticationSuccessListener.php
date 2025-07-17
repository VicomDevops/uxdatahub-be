<?php

namespace App\EventListener;

use App\Entity\Client;
use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Psr\Log\LoggerInterface;
use App\Utils\ParamsHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationSuccessListener
{
    private $loginLogger;
    private $paramsHelper;
    private $requestStack;
    private $userRepository;
    private $sessionRepository;
    private $entityManager;
    private $translator;

    public function __construct(TranslatorInterface $translator,EntityManagerInterface $entityManager,SessionRepository $sessionRepository,UserRepository $userRepository,LoggerInterface $loginLogger, ParamsHelper $paramsHelper, RequestStack $requestStack)
    {
        $this->loginLogger = $loginLogger;
        $this->paramsHelper = $paramsHelper;
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->sessionRepository = $sessionRepository;
        $this->translator = $translator;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $inputs = json_decode($request->getContent(), true);
        $this->paramsHelper->setInputs($inputs);
        $this->paramsHelper->setLogger($this->loginLogger);
        $this->paramsHelper->flushInputWithLogger();
        $user = $this->userRepository->findOneBy(['id' => $event->getUser()]);
        // if ($user instanceof Client && !$user->getIsVerified())
        // {
        //     return $this->countDownRemainingDays($user,$event);
        // }
        $session_id = explode(".", $event->getData()['token']);
        if (!$session = $this->sessionRepository->findOneBy(["user" => $user]))
        {
            $session = new Session();
            $session->setUser($user);
            $session->setSessionId($session_id[1]);
            $session->setCreatedAt(new \DateTime("now"));

        }else
        {
            $session->setSessionId($session_id[1]);
            $session->setUpdatedAt(new \DateTime("now"));
        }
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        $event->setData([
            'header' => [
                'code' => 200,
                'message' => $this->translator->trans('general.success')
            ],
            'response' => [
                'token' => $event->getData()['token']
            ]
        ]);
    }

    private function countDownRemainingDays($user, $event)
    {
        $createdAt = $user->getCreatedAt();
        if ($createdAt instanceof \DateTime) {
            $createdAtDateTime = $createdAt;
        } else {
            $createdAtDateTime = new \DateTime($createdAt);
        }
        $currentDateTime = new \DateTime();
        $interval = $currentDateTime->diff($createdAtDateTime);
        $daysDifference = $interval->days;
        if ($daysDifference == 0) {
            $daysRemaining = 2;
        } elseif ($daysDifference == 1) {
            $daysRemaining = 1;
        } else {
            $daysRemaining = 0;
        }
        $message = $this->translator->trans('users.active_account', [
            'NUMBER' => $daysRemaining
        ]);
        $event->setData(['message' => $message, 'status' => '400 Unauthorized']);
        $event->setData([
            'header' => [
                'code' => 400,
                'message' => $message
            ],
            'response' => null
        ]);
        return $event;
    }
}
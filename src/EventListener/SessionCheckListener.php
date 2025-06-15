<?php


namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Repository\SessionRepository;
use Symfony\Component\Security\Core\Security;

class SessionCheckListener
{
    private $SessionRepository;

    private $security;

    public function __construct(SessionRepository $SessionRepository, Security $security)
    {
        $this->SessionRepository = $SessionRepository;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->getCurrentUser())
        {
            $headers = $event->getRequest()->headers;

            $session = $this->SessionRepository->findOneBy(['user' => $this->getCurrentUser()]);
            if ($headers->has('Authorization'))
            {
                $session_id = explode(".", $headers->get('Authorization'));
                if ($session->getSessionId() != $session_id["1"])
                {
                    $response = new JsonResponse();
                    $JsonResponse = $response->setData([
                        "header" => [
                            "code" => 331,
                            "message" => 'Session expiré !',
                        ],
                        "response" => null
                    ]);
                    $event->setResponse($JsonResponse);
                }else
                {
                    return;
                }
            }else
            {
                return;
            }
        }
        return;
    }

    public function getCurrentUser()
    {
        return $this->security->getUser();
    }
}
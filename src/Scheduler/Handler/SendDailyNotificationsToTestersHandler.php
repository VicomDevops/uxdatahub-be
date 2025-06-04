<?php

namespace App\Scheduler\Handler;

use App\Message\MailMessage;
use App\Repository\NotificationsRepository;
use App\Scheduler\Message\SendDailyNotificationsToTesters;
use App\Service\Mailer;
use App\Service\ResponseService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;


class SendDailyNotificationsToTestersHandler
{
    private $schedulerNotificationsLogger ;
    private $serializer;
    private $mailer;
    private $notificationsRepository;
    private $entityManager;
    private $messageBus;
    private $responseService;

    public function __construct(ResponseService $responseService,MessageBusInterface $messageBus,EntityManagerInterface $entityManager,Mailer $mailer,SerializerInterface $serializer, LoggerInterface $schedulerNotificationsLogger, NotificationsRepository $notificationsRepository)
    {
        $this->schedulerNotificationsLogger  = $schedulerNotificationsLogger ;
        $this->serializer = $serializer;
        $this->mailer = $mailer;
        $this->notificationsRepository = $notificationsRepository;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->responseService = $responseService;
    }

    #[AsCronTask('# # * * *')]
    public function __invoke()
    {
        try {
            $notifys = $this->notificationsRepository->findAll();
            foreach($notifys as $notify)
            {
                if ($notify->getNotificationNumber() < 5 && !$notify->getScenarios()->getIsTested() && $notify->getScenarios()->getEtat() ==3)
                {
                    $notify->setNotificationNumber($notify->getNotificationNumber()+1);
                    $notify->setLastNotifcationDate(new \DateTimeImmutable('now'));
                    $this->entityManager->persist($notify);
                    $this->entityManager->flush();
                    $this->mailer->sendScheduledNotificationClient($notify->getClientTester(),$notify->getScenarios());
                    $this->schedulerNotificationsLogger->info('INFOS ! : '.'User ID: '.$notify->getId().' Scenario : '.$notify->getScenarios()->getTitle(). ' Panel : '.$notify->getPanel()->getName());
                }else
                {
                    $this->schedulerNotificationsLogger->info('INFOS DONE ! : '.'User ID: '.$notify->getId().' Scenario : '.$notify->getScenarios()->getTitle(). ' Panel : '.$notify->getPanel()->getName());
                }
            }

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            $this->schedulerNotificationsLogger->error('ERROR ! : '.$exception->getMessage());
            return $this->responseService->getResponseToClient();
        }
    }
}
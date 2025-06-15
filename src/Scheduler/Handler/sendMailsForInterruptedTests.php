<?php

namespace App\Scheduler\Handler;

use App\Repository\TestRepository;
use App\Service\Mailer;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Serializer\SerializerInterface;

class sendMailsForInterruptedTests
{
    private $interruptedTestsLogger;
    private $serializer;
    private $mailer;
    private $testRepository;
    private $entityManager;
    private $messageBus;
    private $responseService;

    public function __construct(ResponseService $responseService,MessageBusInterface $messageBus,EntityManagerInterface $entityManager,Mailer $mailer,SerializerInterface $serializer, LoggerInterface $interruptedTestsLogger, TestRepository $testRepository)
    {
        $this->interruptedTestsLogger  = $interruptedTestsLogger ;
        $this->serializer = $serializer;
        $this->mailer = $mailer;
        $this->testRepository = $testRepository;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->responseService = $responseService;
    }

    #[AsCronTask('0 8 * * *')]
    public function __invoke():JsonResponse
    {
        try {
            $interruptedTests = $this->testRepository->findBy(["isInterrupted" => true]);
            foreach($interruptedTests as $interruptedTest)
            {
                $this->mailer->sendMailToTestersForInterruptedTests($interruptedTest);
                $this->interruptedTestsLogger->info('INFOS ! : '.'Tester : '.$interruptedTest->getClientTester()->getName(). ' '.$interruptedTest->getClientTester()->getLastname().' Test ID: '.$interruptedTest->getId().' Scenario : '.$interruptedTest->getScenarios()->getTitle(). ' Panel : '.$interruptedTest->getPanel()->getName());
            }

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            $this->interruptedTestsLogger->error('ERROR ! : '.$exception->getMessage());
            return $this->responseService->getResponseToClient();
        }
    }

}
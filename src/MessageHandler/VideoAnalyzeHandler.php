<?php

namespace App\MessageHandler;

use App\Message\VideoAnalyzeMessage;
use App\Repository\TestRepository;
use App\Service\VideoAnalyze;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VideoAnalyzeHandler implements MessageHandlerInterface
{
    private $videoAnalyze;
    private $testRepository;
    private $googleApisLogger;
    private $serializer;

    public function __construct(SerializerInterface $serializer,VideoAnalyze $videoAnalyze,TestRepository $testRepository, LoggerInterface $googleApisLogger)
    {
        $this->videoAnalyze = $videoAnalyze;
        $this->testRepository = $testRepository;
        $this->googleApisLogger = $googleApisLogger;
        $this->serializer = $serializer;
    }
    public function __invoke(VideoAnalyzeMessage $message)
    {
        $testId = $message->getTestId();
        $test = $this->testRepository->findOneBy(['id'=>$testId]);
        $this->googleApisLogger->info('Test Before Analyze :' .$this->serializer->serialize($test, 'json', ['groups' => 'get_test']));
        if($test)
        {
            $this->googleApisLogger->info('TEST IS STARTING TO ANALYZE :' .$this->serializer->serialize($test, 'json', ['groups' => 'get_test']));
            $this->videoAnalyze->analyze($test);
        }else
        {
            $this->googleApisLogger->info('ERROR : No Test found !');
        }
    }

}
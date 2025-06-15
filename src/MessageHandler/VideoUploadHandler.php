<?php

namespace App\MessageHandler;

use App\Message\VideoUploadMessage;
use App\Service\TestsServices;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class VideoUploadHandler implements MessageHandlerInterface
{

    private $testsServices;

    public function __construct(TestsServices $testsServices)
    {

        $this->testsServices = $testsServices;
    }

    public function __invoke(VideoUploadMessage $message)
    {
        try {
           return $this->testsServices->asychUpload($message->getFile(),$message->getTestId(),$message->getAnswerId(),$message->getDuration());

        } catch (\Exception $exception)
        {
            return $exception->getMessage();
        }
    }
}
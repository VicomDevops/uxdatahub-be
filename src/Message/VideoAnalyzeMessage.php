<?php

namespace App\Message;

class VideoAnalyzeMessage
{
    private $testId;

    public function __construct(int $testId)
    {
        $this->testId = $testId;
    }
    public function getTestId():int
    {
     return $this->testId;
    }

}
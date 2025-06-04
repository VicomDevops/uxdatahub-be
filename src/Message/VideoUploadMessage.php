<?php

namespace App\Message;

class VideoUploadMessage
{
    private $file;
    private $test_id;
    private $answer_id;
    private $duration;

    public function __construct($file,$test_id, $answer_id,$duration)
    {
        $this->file = $file;
        $this->test_id = $test_id;
        $this->answer_id = $answer_id;
        $this->duration = $duration;
    }

    public function getFile()
    {
        return $this->file;
    }
    public function getTestId()
    {
        return $this->test_id;
    }

    public function getAnswerId()
    {
        return $this->answer_id;
    }

    public function getDuration()
    {
        return $this->duration;
    }

}
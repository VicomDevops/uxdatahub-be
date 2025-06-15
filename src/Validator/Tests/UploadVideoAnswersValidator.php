<?php

namespace App\Validator\Tests;

use Symfony\Component\Validator\Constraints as Assert;

class UploadVideoAnswersValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $file;

    /**
     * @Assert\NotBlank()
     */
    private $test_id;

    /**
     * @Assert\NotBlank()
     */
    private $answer_id;

    /**
     * @Assert\NotBlank()
     */
    private $duration;

    public function __construct(array $params)
    {
        $this->file = @$params['file'];
        $this->test_id = @$params['test_id'];
        $this->answer_id = @$params['answer_id'];
        $this->duration = @$params['duration'];
    }
}
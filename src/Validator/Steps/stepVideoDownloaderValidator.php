<?php

namespace App\Validator\Steps;

use Symfony\Component\Validator\Constraints as Assert;

class stepVideoDownloaderValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $answer_id;

    public function __construct(array $params)
    {
        $this->answer_id = @$params['answer_id'];
    }

}
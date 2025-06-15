<?php

namespace App\Validator\OpenAI;

use Symfony\Component\Validator\Constraints as Assert;

class OpenAIAnalysesChatValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $messages;

    public function __construct(array $params)
    {
        $this->messages = @$params['messages'];
    }

}
<?php

namespace App\Validator\Steps;

use Symfony\Component\Validator\Constraints as Assert;

class AnalyzeByStepValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $step_id;

    public function __construct(array $params)
    {
        $this->step_id = @$params['step_id'];
    }
}
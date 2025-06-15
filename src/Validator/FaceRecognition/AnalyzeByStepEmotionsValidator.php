<?php

namespace App\Validator\FaceRecognition;

use Symfony\Component\Validator\Constraints as Assert;
class AnalyzeByStepEmotionsValidator
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
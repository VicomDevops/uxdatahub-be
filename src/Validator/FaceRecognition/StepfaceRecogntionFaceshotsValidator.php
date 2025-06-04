<?php

namespace App\Validator\FaceRecognition;

use Symfony\Component\Validator\Constraints as Assert;
class StepfaceRecogntionFaceshotsValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $tester_id;

    /**
     * @Assert\NotBlank()
     */
    private $answer_id;


    public function __construct(array $params)
    {
        $this->tester_id = @$params['tester_id'];
        $this->answer_id = @$params['answer_id'];
    }

}
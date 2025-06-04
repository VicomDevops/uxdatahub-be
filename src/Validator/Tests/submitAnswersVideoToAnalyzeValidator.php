<?php

namespace App\Validator\Tests;

use Symfony\Component\Validator\Constraints as Assert;

class submitAnswersVideoToAnalyzeValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $idtest;


    /**
     * @Assert\NotBlank()
     */
    private $ended;

    /**
     * @Assert\NotBlank()
     */
    private $idscenario;

    /**
     * @Assert\NotBlank()
     */
    private $answers;

    /**
     * @Assert\NotBlank()
     */
    private $step_id;


    public function __construct(array $params)
    {
        $this->idtest = @$params['idtest'];
        $this->ended = @$params['ended'];
        $this->idscenario = @$params['idscenario'];
        $this->answers = @$params['answers'];
        $this->step_id = @$params['step_id'];
    }
}
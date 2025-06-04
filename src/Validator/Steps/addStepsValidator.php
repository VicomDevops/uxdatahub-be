<?php

namespace App\Validator\Steps;

use Symfony\Component\Validator\Constraints as Assert;

class addStepsValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $idstep;

    /**
     * @Assert\NotBlank()
     */
    private $payloads;

    public function __construct(array $params)
    {
        $this->idstep = @$params['idstep'];
        $this->payloads = @$params['payloads'];
    }
}
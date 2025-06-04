<?php

namespace App\Validator\Tests;

use Symfony\Component\Validator\Constraints as Assert;

class analyzedAnswersByTestValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $idtest;

    public function __construct(array $params)
    {
        $this->idtest = @$params['idtest'];
    }
}
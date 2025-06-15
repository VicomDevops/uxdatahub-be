<?php

namespace App\Validator\Scenarios;

use Symfony\Component\Validator\Constraints as Assert;


class ScenarioDetailsValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $id;


    public function __construct(array $params)
    {
        $this->id = @$params['id'];
    }
}
<?php

namespace App\Validator\Scenarios;

use Symfony\Component\Validator\Constraints as Assert;

class ScenarioNameValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $scenario_name;


    public function __construct(array $params)
    {
        if (trim(@$params['scenario_name']) === '')
        {
            @$params['scenario_name'] = '';
        }
        $this->scenario_name = @$params['scenario_name'];
    }
}
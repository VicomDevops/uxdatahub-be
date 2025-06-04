<?php

namespace App\Validator\Scenarios;

use Symfony\Component\Validator\Constraints as Assert;

class StepsDetailsByScenarioValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;



    public function __construct(array $params)
    {
        $this->scenario_id = @$params['scenario_id'];
    }

}
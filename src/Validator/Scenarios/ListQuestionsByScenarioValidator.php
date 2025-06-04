<?php

namespace App\Validator\Scenarios;

use Symfony\Component\Validator\Constraints as Assert;

class ListQuestionsByScenarioValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;

    /**
     * @Assert\NotBlank()
     */
    private $test_id;


    public function __construct(array $params)
    {
        $this->scenario_id = @$params['scenario_id'];
        $this->test_id = @$params['test_id'];
    }

}
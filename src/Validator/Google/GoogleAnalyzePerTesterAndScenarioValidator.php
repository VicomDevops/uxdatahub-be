<?php

namespace App\Validator\Google;

use Symfony\Component\Validator\Constraints as Assert;

class GoogleAnalyzePerTesterAndScenarioValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;

    /**
     * @Assert\NotBlank()
     */
    private $tester_id;


    public function __construct(array $params)
    {
        $this->scenario_id = @$params['scenario_id'];
        $this->tester_id = @$params['tester_id'];
    }

}
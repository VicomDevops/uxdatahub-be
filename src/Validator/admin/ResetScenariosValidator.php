<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;

class ResetScenariosValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $tester_id;

    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;

    public function __construct(array $params)
    {
        $this->tester_id = @$params['tester_id'];
        $this->scenario_id = @$params['scenario_id'];
    }
}
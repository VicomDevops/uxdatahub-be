<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class encloseScenariosValidator
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
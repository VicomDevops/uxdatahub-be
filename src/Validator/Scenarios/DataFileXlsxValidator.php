<?php

namespace App\Validator\Scenarios;

use Symfony\Component\Validator\Constraints as Assert;

class DataFileXlsxValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;

    /**
     * @Assert\NotBlank()
     */
    private $testers_id;


    public function __construct(array $params)
    {
        $this->scenario_id = @$params['scenario_id'];
        $this->testers_id = @$params['testers_id'];
    }

}
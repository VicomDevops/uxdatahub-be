<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class panelTestersStatisticsValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;

    /**
     * @Assert\NotBlank()
     */
    private $filter;


    public function __construct(array $params)
    {
        $this->scenario_id = @$params['scenario_id'];
        $this->filter = @$params['filter'];
    }

}
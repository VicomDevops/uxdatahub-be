<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class PanelAssignScenarioValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $panel_id;

    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;



    public function __construct(array $params)
    {
        $this->panel_id = @$params['panel_id'];
        $this->scenario_id = @$params['scenario_id'];
    }

}
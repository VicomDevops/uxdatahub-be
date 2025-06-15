<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class unpassedClientTesterScenariosValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $client_tester_id;

    /**
     * @Assert\NotBlank()
     */
    private $panel_id;



    public function __construct(array $params)
    {
        $this->client_tester_id = @$params['client_tester_id'];
        $this->panel_id = @$params['panel_id'];
    }

}
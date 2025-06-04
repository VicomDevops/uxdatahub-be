<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteClientTesterValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $panel_id;

    /**
     * @Assert\NotBlank()
     */
    private $client_tester_id;


    public function __construct(array $params)
    {
        $this->panel_id = @$params['panel_id'];
        $this->client_tester_id = @$params['client_tester_id'];
    }
}
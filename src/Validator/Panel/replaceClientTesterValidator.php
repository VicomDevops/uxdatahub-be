<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class replaceClientTesterValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $current_client_tester_id;

    /**
     * @Assert\NotBlank()
     */
    private $current_panel_id;

    /**
     * @Assert\NotBlank()
     */
    private $new_email;

    /**
     * @Assert\NotBlank()
     */
    private $new_name;

    /**
     * @Assert\NotBlank()
     */
    private $new_lastname;

    public function __construct(array $params)
    {
        $this->current_client_tester_id = @$params['current_client_tester_id'];
        $this->current_panel_id = @$params['current_panel_id'];
        $this->new_email = @$params['new_email'];
        $this->new_name = @$params['new_name'];
        $this->new_lastname = @$params['new_lastname'];
    }
}
<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class addClientTesterValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $panel_id;

    /**
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Assert\NotBlank()
     */
    private $lastname;

    public function __construct(array $params)
    {
        $this->panel_id = @$params['panel_id'];
        $this->email = @$params['email'];
        $this->name = @$params['name'];
        $this->lastname = @$params['lastname'];
    }

}
<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateClientTesterValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $id;

    /**
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @Assert\NotBlank()
     */
    private $email;

    public function __construct(array $params)
    {
        $this->id = @$params['id'];
        $this->name = @$params['name'];
        $this->lastname = @$params['lastname'];
        $this->email = @$params['email'];
    }

}
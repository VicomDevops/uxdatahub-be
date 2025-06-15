<?php

namespace App\Validator\Profile;

use Symfony\Component\Validator\Constraints as Assert;

class ClientTesterUpdateProfileValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @Assert\NotBlank()
     */
    private $lastname;


    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->name = @$params['name'];
        $this->email = @$params['email'];
        $this->city = @$params['city'];
        $this->phone = @$params['phone'];
        $this->lastname = @$params['lastname'];
    }
}
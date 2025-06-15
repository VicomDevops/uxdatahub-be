<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserVerifyEmailValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $email;


    public function __construct(array $params)
    {
        $this->email = @$params['email'];
    }
}
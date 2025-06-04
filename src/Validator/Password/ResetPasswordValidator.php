<?php

namespace App\Validator\Password;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @Assert\NotBlank()
     */
    private $repassword;

    /**
     * @Assert\NotBlank()
     */
    private $token;

    public function __construct(array $params)
    {
        $this->password = @$params['password'];
        $this->repassword = @$params['repassword'];
        $this->token = @$params['token'];
    }

}
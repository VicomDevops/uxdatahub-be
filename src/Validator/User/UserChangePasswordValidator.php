<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserChangePasswordValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $old_password;

    /**
     * @Assert\NotBlank()
     */
    private $new_password;

    public function __construct(array $params)
    {
        $this->old_password = @$params['old_password'];
        $this->new_password = @$params['new_password'];
    }
}
<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class VerifyMailValidator
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
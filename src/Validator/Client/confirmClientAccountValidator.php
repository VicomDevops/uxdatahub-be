<?php

namespace App\Validator\Client;

use Symfony\Component\Validator\Constraints as Assert;
class confirmClientAccountValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $token;


    public function __construct(array $params)
    {
        $this->token = @$params['token'];
    }

}
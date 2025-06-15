<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;

class clientValidationValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $client_id;

    public function __construct(array $params)
    {
        $this->client_id = @$params['client_id'];
    }
}
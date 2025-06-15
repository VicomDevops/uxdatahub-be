<?php

namespace App\Validator\Tests;

use Symfony\Component\Validator\Constraints as Assert;

class StartTestValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $id;


    public function __construct(array $params)
    {
        $this->id = @$params['id'];
    }
}
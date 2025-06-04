<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;

class testerValidationValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $tester_id;

    public function __construct(array $params)
    {
        $this->tester_id = @$params['tester_id'];
    }
}
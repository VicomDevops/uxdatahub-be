<?php

namespace App\Validator\Tests;

use Symfony\Component\Validator\Constraints as Assert;

class ResetTestWhenTesterInterruptValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $test_id;


    public function __construct(array $params)
    {
        $this->test_id = @$params['test_id'];
    }

}
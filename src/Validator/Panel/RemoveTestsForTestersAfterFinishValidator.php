<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class RemoveTestsForTestersAfterFinishValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $tests_id;


    public function __construct(array $params)
    {
        $this->tests_id = @$params['tests_id'];
    }

}
<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;

class reanalyzeTestValidator
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
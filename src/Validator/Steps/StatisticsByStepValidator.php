<?php

namespace App\Validator\Steps;

use Symfony\Component\Validator\Constraints as Assert;

class StatisticsByStepValidator
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
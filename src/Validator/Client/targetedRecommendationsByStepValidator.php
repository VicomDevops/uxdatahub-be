<?php

namespace App\Validator\Client;

use Symfony\Component\Validator\Constraints as Assert;
class targetedRecommendationsByStepValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $scenario_id;


    public function __construct(array $params)
    {
        $this->scenario_id = @$params['scenario_id'];
    }

}
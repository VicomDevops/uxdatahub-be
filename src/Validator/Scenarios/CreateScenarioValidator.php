<?php

namespace App\Validator\Scenarios;

use Symfony\Component\Validator\Constraints as Assert;

class CreateScenarioValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $isModerate;

    /**
     * @Assert\NotBlank()
     */
    private $isUnique;

    /**
     * @Assert\NotBlank()
     */
    private $langue;

    /**
     * @Assert\NotBlank()
     */
    private $product;

    /**
     * @Assert\NotBlank()
     */
    private $title;


    public function __construct(array $params)
    {
        $this->isModerate = @$params['isModerate'];
        $this->isUnique = @$params['isUnique'];
        $this->langue = @$params['langue'];
        $this->product = @$params['product'];
        $this->title = @$params['title'];
    }

}
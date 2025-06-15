<?php

namespace App\Validator\Profile;

use Symfony\Component\Validator\Constraints as Assert;


class UpdateProfilePicValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $img;


    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->img = @$params['img'];
    }

}
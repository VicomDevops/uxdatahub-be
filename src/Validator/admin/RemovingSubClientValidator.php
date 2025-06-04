<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;
class RemovingSubClientValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $subclient_id;

    public function __construct(array $params)
    {
        $this->subclient_id = @$params['subclient_id'];
    }
}
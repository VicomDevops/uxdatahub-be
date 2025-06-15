<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;

class RemovingAdminValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $admin_id;

    public function __construct(array $params)
    {
        $this->admin_id = @$params['admin_id'];
    }
}
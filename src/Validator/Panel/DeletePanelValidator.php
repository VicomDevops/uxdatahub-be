<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class DeletePanelValidator
{

    /**
     * @Assert\NotBlank()
     */
    private $panel_id;


    public function __construct(array $params)
    {
        $this->panel_id = @$params['panel_id'];
    }
}
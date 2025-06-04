<?php

namespace App\Validator\Panel;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePanelValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $panel_id;


    /**
     * @Assert\NotBlank()
     */
    private $panel;


    public function __construct(array $params)
    {
        $this->panel_id = @$params['panel_id'];
        $this->panel = @$params['panel'];
    }

}
<?php

namespace App\Service;

use Symfony\Component\Validator\Constraints\IsNull;

class MathematicalFunctionsService
{

    public function __construct()
    {
        
    }

    public function pourcentage($number,$total,$pourcentage)
    {
        if(is_null($total) || $total == 0)
        {
            return 0;
        }else
        {
            return round(($number/$total)*$pourcentage);
        }

    }
}
<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class BadResponseExternalWebserviceException extends \Exception implements ApiExceptionInterface
{
    public function __construct($message = null)
    {
        parent::__construct($message, Response::HTTP_OK);
    }
}
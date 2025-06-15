<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationErrors
{

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function getErrors($entity)
    {
//        /** @var ConstraintViolation [] $errors */
        $errors = $this->validator->validate($entity);
        $errorsArray = [];
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
        }

        return $errorsArray;
    }
}
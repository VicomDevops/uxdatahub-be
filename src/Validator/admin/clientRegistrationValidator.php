<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
class clientRegistrationValidator
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $lastname;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $useCase;

    /**
     * @Assert\NotBlank()
     */
    private $nbEmployees;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $sector;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $profession;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $phone;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $company;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("bool")
     */
    private $cgu;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("bool")
     */
    private $privacyPolicy;

    public function __construct(array $params)
    {
        $this->name = @$params['name'];
        $this->lastname = @$params['lastname'];
        $this->useCase = @$params['useCase'];
        $this->nbEmployees = @$params['nbEmployees'];
        $this->sector = @$params['sector'];
        $this->profession = @$params['profession'];
        $this->email = @$params['email'];
        $this->phone = @$params['phone'];
        $this->company = @$params['company'];
        $this->cgu = @$params['cgu'];
        $this->privacyPolicy = @$params['privacyPolicy'];
    }

}
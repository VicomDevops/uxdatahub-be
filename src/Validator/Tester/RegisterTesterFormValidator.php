<?php

namespace App\Validator\Tester;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterTesterFormValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @Assert\NotBlank()
     */
    private $gender;

    /**
     * @Assert\NotBlank()
     */
    private $country;

    /**
     * @Assert\NotBlank()
     */
    private $ville;

    /**
     * @Assert\NotBlank()
     */
    private $csp;

    /**
     * @Assert\NotBlank()
     */
    private $studyLevel;

    /**
     * @Assert\NotBlank()
     */
    private $maritalStatus;

    /**
     * @Assert\NotBlank()
     */
    private $adressePostal;

    /**
     * @Assert\NotBlank()
     */
    private $codePostal;

    /**
     * @Assert\NotBlank()
     */
    private $socialMedia;

    /**
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @Assert\NotBlank()
     */
    private $dateOfBirth;

    /**
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @Assert\NotBlank()
     */
    private $cgu;

    /**
     * @Assert\NotBlank()
     */
    private $privacyPolicy;

    /**
     * @Assert\NotBlank()
     */
    private $identityCardFront;

    /**
     * @Assert\NotBlank()
     */
    private $identityCardBack;

    public function __construct(array $params)
    {
        $this->name = @$params['name'];
        $this->lastname = @$params['lastname'];
        $this->gender = @$params['gender'];
        $this->country = @$params['country'];
        $this->ville = @$params['ville'];
        $this->csp = @$params['csp'];
        $this->studyLevel = @$params['studyLevel'];
        $this->maritalStatus = @$params['maritalStatus'];
        $this->adressePostal = @$params['adressePostal'];
        $this->codePostal = @$params['codePostal'];
        $this->socialMedia = @$params['socialMedia'];
        $this->email = @$params['email'];
        $this->dateOfBirth = @$params['dateOfBirth'];
        $this->phone = @$params['phone'];
        $this->cgu = @$params['cgu'];
        $this->privacyPolicy = @$params['privacyPolicy'];
        $this->identityCardFront = @$params['identityCardFront'];
        $this->identityCardBack = @$params['identityCardBack'];
    }

}
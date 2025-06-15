<?php

namespace App\Validator\Profile;

use Symfony\Component\Validator\Constraints as Assert;

class ClientTesterCompleteProfileValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $gender;

    /**
     * @Assert\NotBlank()
     */
    private $csp;

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
    private $country;

    /**
     * @Assert\NotBlank()
     */
    private $id;

    /**
     * @Assert\NotBlank()
     */
    private $os;

    /**
     * @Assert\NotBlank()
     */
    private $osMobile;

    /**
     * @Assert\NotBlank()
     */
    private $osTablet;

    /**
     * @Assert\NotBlank()
     */
    private $postalCode;

    /**
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @Assert\NotBlank()
     */
    private $adresse;

    /**
     * @param array $params
     */

    public function __construct(array $params)
    {
        $this->gender = @$params['gender'];
        $this->csp = @$params['csp'];
        $this->dateOfBirth = @$params['dateOfBirth'];
        $this->phone = @$params['phone'];
        $this->country = @$params['country'];
        $this->id = @$params['id'];
        $this->os = @$params['os'];
        $this->osMobile = @$params['osMobile'];
        $this->osTablet = @$params['osTablet'];
        $this->postalCode = @$params['postalCode'];
        $this->city = @$params['city'];
        $this->adresse = @$params['adresse'];
    }

}
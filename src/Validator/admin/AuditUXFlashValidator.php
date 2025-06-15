<?php

namespace App\Validator\admin;

use Symfony\Component\Validator\Constraints as Assert;

class AuditUXFlashValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $clientName;

    /**
     * @Assert\NotBlank()
     */
    private $workField;


    /**
     * @Assert\NotBlank()
     */
    private $scenarioName;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $url;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $competingUrl1;

    /**
     * @Assert\NotBlank()
     */
    private $competingName1;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $competingUrl2;

    /**
     * @Assert\NotBlank()
     */
    private $competingName2;

    public function __construct(array $params)
    {
        $this->clientName = @$params['clientName'];
        $this->workField = @$params['workField'];
        $this->scenarioName = @$params['scenarioName'];
        $this->url = @$params['url'];
        $this->competingUrl1 = @$params['competingUrl1'];
        $this->competingName1 = @$params['competingName1'];
        $this->competingUrl2 = @$params['competingUrl2'];
        $this->competingName2 = @$params['competingName2'];
    }
}
<?php

namespace App\Message;

class MailMessage
{
    private $tester;

    private $scenario;

    public function __construct($tester,$scenario)
    {
        $this->tester = $tester;
        $this->scenario = $scenario;
    }
    public function getClientTester()
    {
        return $this->tester;
    }

    public function getScenarios()
    {
        return $this->scenario;
    }

}
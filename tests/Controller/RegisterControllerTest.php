<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterControllerTest extends WebTestCase
{
    const RIGHT_DATA = '{
            "name":"Wassim", 
            "lastname": "Zouaoui", 
            "company": "2m-advisory", 
            "email": "zwas@2m-advisory.fr",
            "useCase": "Entreprise: Plusieurs projets à tester",
            "nbEmployees": "11-50",
            "phone": "25707027",
            "profession": "Developer",
            "sector": "IT"
            }';

    const WRONG_DATA = '{
            "name":"Wassim", 
            "lastname": "Zouaoui", 
            "company": "2m-advisory", 
            "email": "zwas@2m-advisory.fr",
            "useCase": "Entreprise: Plusieurs projets à tester",
            "nbEmployees": "11-50",
            "phone": "25707027",
            "sector": ""
            }';

    public function testSignupWithMessingFields()
    {
        $client = self::createClient();

        $client->request('POST', '/signup-client',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::WRONG_DATA);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testSignupWithCorrectData()
    {
        $client = self::createClient();

        $client->request('POST', '/signup-client',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::RIGHT_DATA);

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testSignupWithExistingUser()
    {
        $client = self::createClient();

        $client->request('POST', '/signup-client',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::RIGHT_DATA);

        $this->assertEquals(409, $client->getResponse()->getStatusCode());
    }
}
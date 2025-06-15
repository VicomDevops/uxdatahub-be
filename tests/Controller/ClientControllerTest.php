<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientControllerTest extends WebTestCase
{
    public function testGetNewClientsNotAuthorized()
    {
        $client = $this->createAuthenticatedClient('client@client.com', 'client123');
        $client->request('GET', '/api/clients');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testGetNewClients()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/clients');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testValidateClientWithNotExisting()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/clients/245/validate');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testValidateClient()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/clients/10/validate');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    protected function createAuthenticatedClient($username = 'admin@admin.com', $password = 'admin123')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login_check',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"username": "' . $username . '", "password": "' . $password . '"}'
        );


        $data = json_decode($client->getResponse()->getContent(), true);

//        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }
}
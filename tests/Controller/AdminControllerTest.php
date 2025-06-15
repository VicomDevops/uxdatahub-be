<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testGetAllAdminsWithoutLogin()
    {
        $client = self::createClient();
        $client->request('GET', '/api/admins');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testGetAllAdminsWithoutRoleAdmin()
    {
        $client = $this->createAuthenticatedClient('client@client.com', 'client123');
        $client->request('GET', '/api/admins');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testGetAllAdminsWithRoleAdmin()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admins');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCreateAdminWithMissingData()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/admins',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"email": "admin1@admin.com", "username": "admin1234"}');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testCreateAdminWithExistingEmail()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/admins',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"admin", "lastname": "adminlast", "email": "admin@admin.com", "username": "admin1234"}');

        $this->assertEquals(409, $client->getResponse()->getStatusCode());
    }

    public function testCreateAdminWithGoodData()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/admins',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"admin", "lastname": "adminlast", "email": "admin1@admin.com", "username": "admin1234"}');

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testRegeneratePasswordWithNoExistingUser()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admins/100/new-password');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testRegeneratePassword()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admins/10/new-password');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testDesactivateUserNotExisting()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admins/1000/desactivate-user');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDesactivateUser()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admins/10/desactivate-user');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testDeleteAdmin()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admins/2');

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
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
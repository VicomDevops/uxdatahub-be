<?php

namespace App\Tests\Service;

use App\Service\PasswordGenerator;
use PHPUnit\Framework\TestCase;

class PasswordGeneratorTest extends TestCase
{
    public function testGeneratePassword()
    {
        $passwordGenerator = new PasswordGenerator();

        $result = $passwordGenerator->generatePassword(10);
        $this->assertEquals(10, strlen($result));
    }
}
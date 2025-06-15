<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class PasswordGenerator
{

    private $passwordHasher;

    private $userRepository;

    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    public function generatePassword($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    public function newPassword(User $user)
    {
        $password = $this->generatePassword(10);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $this->userRepository->upgradePassword($user, $hashedPassword);
        $user->setPassword($hashedPassword);
        return $password;
    }

    public function generateUsername(User $user)
    {
        $username = $user->getLastname().rand(1000,9999);
        if ($this->userRepository->findOneBy(['username' => $username])) {
            $this->generateUsername($user);
        }

        $user->setUsername($username);
        return $username;
    }
}
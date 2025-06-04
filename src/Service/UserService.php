<?php

namespace App\Service;

use App\Entity\Test;
use App\Repository\PanelRepository;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService
{
    private $passwordEncoder;
    private $userRepository;
    private $entityManager;
    private $serializer;
    private $responseService;
    private $tokenStorage;
    private $paramsHelper;
    private $translator;

    public function __construct(TranslatorInterface $translator,ParamsHelper $paramsHelper,UserPasswordHasherInterface $passwordEncoder,UserRepository $userRepository,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository=$userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->paramsHelper = $paramsHelper;
        $this->translator = $translator;
    }

    public function changePasswordValidation()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            if (strlen($inputs['new_password'])< 8)
            {
                return $this->responseService->getResponseToClient(null, 201, 'password.password_regex');
            }
            $user = $this->userRepository->findOneBy(['id' => $this->getTokenStorageUser()->getUser()]);
            $checkPass = $this->passwordEncoder->isPasswordValid($user, $inputs['old_password']);
            if($checkPass) {
                $new_pwd_encoded = $this->passwordEncoder->hashPassword($user, $inputs['new_password']);
                $this->userRepository->upgradePassword($user,$new_pwd_encoded);
            }else
            {
                return $this->responseService->getResponseToClient(null,201,"password.not_correct");
            }

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function verifyUserEmailProfile()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->userRepository->findOneBy(['email' => $inputs["email"]]);
            if($user) {
                return $this->responseService->getResponseToClient(null,201,"mail.not_valid");
            }

            return $this->responseService->getResponseToClient(null,200,"mail.valid");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getCurrentUser()
    {
        try {
            $userData = $this->serializer->serialize($this->getTokenStorageUser()->getUser(), 'json', ["groups" => "current_user"]);
            $interrupted = $this->entityManager->getRepository(Test::class)->findBy(['clientTester' => $this->getTokenStorageUser()->getUser(), 'isInterrupted' => true]);
            $interruptedTests = array_map(function($item) {
                return $item->getScenario()->getTitle();
            }, $interrupted);
            $data = json_decode($userData, true);
            $data['interrupted_tests']= $interruptedTests;
            try
            {
                if (isset($data['profileImage']))
                {
                    $data['profileImage']  = base64_encode(file_get_contents($data['profileImage']));
                }else
                {
                    $data['profileImage'] = null;
                }

            }catch (\Exception $exception)
            {
                $data['profileImage'] = null;
            }

            return $this->responseService->getResponseToClient($data);

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(),500,"general.500");
        }
    }

    public function getTokenStorageUser()
    {
        return $this->tokenStorage->getToken();
    }
}
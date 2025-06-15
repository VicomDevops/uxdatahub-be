<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class PasswordService
{
    private $responseService;
    private $paramsHelper;
    private $jwtManager;
    private $baseUrl;
    private $urlGenerator;
    private $userRepository;
    private $passwordEncoder;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, ParameterBagInterface $params, ParamsHelper $paramsHelper , ResponseService $responseService, JWTTokenManagerInterface $jwtManager, RequestStack $requestStack, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function resetPasswordOffline()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            if ($inputs['password'] != $inputs['repassword'])
            {
                return $this->responseService->getResponseToClient(null, 201, 'password.confirm_password');
            }elseif (strlen($inputs['password'])< 8)
            {
                return $this->responseService->getResponseToClient(null, 201, 'password.password_regex');
            }
            $tokenParts = explode(".", $inputs['token']);
            $tokenPayload = base64_decode($tokenParts[1]);
            $jwtPayload = json_decode($tokenPayload);
            $user = $this->userRepository->findOneBy(['email'=>$jwtPayload->username]);
            $user->setPassword($this->passwordEncoder->hashPassword(
                $user,
                $inputs['password']
            ));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->responseService->getResponseToClient(null,200,'password.reset_success');

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient(null, 500, 'general.500');
        }

    }

    public function generateUrl($user)
    {
        try {
            $token = $this->jwtManager->create($user);
            $url = $this->params->get('FRONT_URL')."/respass"."?token=".$token;

            return $this->responseService->getResponseToClient($url);
        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }
    }

    public function tempresetPassword()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $user = $this->userRepository->findOneBy(['id'=> $inputs["id"]]);
            $user->setPassword($this->passwordEncoder->hashPassword(
                $user,
                $inputs['password']
            ));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->responseService->getResponseToClient(null,200,'password.reset_success');

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, 'general.500');
        }

    }

}
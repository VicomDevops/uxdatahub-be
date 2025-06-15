<?php

namespace App\Service;

use App\Entity\ClientTester;
use App\Repository\ScenarioRepository;
use App\Repository\TestRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ClientTesterProfileService
{
    private $entityManager;
    private $serializer;
    private $testRepository;
    private $responseService;
    private $tokenStorage;
    private $normalizer;
    private $paramsHelper;
    private $uploadService;

    public function __construct(UploadService $uploadService,ParamsHelper $paramsHelper,NormalizerInterface $normalizer,TokenStorageInterface $tokenStorage,ResponseService $responseService,EntityManagerInterface $entityManager, SerializerInterface $serializer,TestRepository $testRepository)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->testRepository = $testRepository;
        $this->responseService = $responseService;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->paramsHelper = $paramsHelper;
        $this->uploadService = $uploadService;
    }

    public function completeProfile()
    {
        try
        {
            $inputs = $this->paramsHelper->getInputs();
            $clientTester = $this->getCurrentUser()->getUser();
            if (!$clientTester instanceof ClientTester)
            {
                return $this->responseService->getResponseToClient(null, 403,"general.forbidden");
            }
            $this->serializer->deserialize(json_encode($inputs, true), ClientTester::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $clientTester]);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient();
        }catch(\Exception $exception){
            return $this->responseService->getResponseToClient($exception->getMessage(),500, "general.500");
        }
    }

    public function updateProfile()
    {
        try
        {
            $clientTester = $this->getCurrentUser()->getUser();
            if (!$clientTester instanceof ClientTester)
            {
                return $this->responseService->getResponseToClient(null, 403,"general.forbidden");
            }
            $inputs = $this->paramsHelper->getInputs();
            foreach ($inputs as $key => $value) {
                if (property_exists(ClientTester::class, $key)) {
                    $clientTester->{'set' . ucfirst($key)}($value);
                }
            }
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient();
        }catch(\Exception $exception){
            return $this->responseService->getResponseToClient($exception->getMessage(),500, "general.500");
        }
    }

    public function updateProfilePhoto()
    {
        try{
            $clientTester = $this->getCurrentUser()->getUser();
            if (!$clientTester instanceof ClientTester)
            {
                return $this->responseService->getResponseToClient(null, 403,"general.forbidden");
            }
            $inputs = $this->paramsHelper->getInputs();
            $profileImage = $this->uploadService->uploadProfileImage($inputs["img"],$clientTester);
            $clientTester->setProfileImage($profileImage);
            $this->entityManager->persist($clientTester);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient();
        }catch(\Exception $exception){
            return $this->responseService->getResponseToClient($exception->getMessage(),500, "general.500");
        }
    }

    public function getCurrentUser()
    {
        return $this->tokenStorage->getToken();
    }
}
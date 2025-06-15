<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Scenario;
use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StepsServices
{
    private $responseService;
    private $paramsHelper;
    private $serializer;
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag,SerializerInterface $serializer,ResponseService $responseService, ParamsHelper $paramsHelper, EntityManagerInterface $entityManager)
    {
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->parameterBag = $parameterBag;
    }
    public function getVideo()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $answer = $this->entityManager->getRepository(Answer::class)->findOneBy(['id' => $inputs['answer_id']]);
            if (is_null($answer) || is_null($answer->getVideo()))
            {
                return $this->responseService->getResponseToClient(null,404,"file.not_exist");
            }
            $fileName = explode("/",$answer->getVideo());
            $response = new BinaryFileResponse($this->parameterBag->get('video_path_download').end($fileName));
            $response->headers->set("Content-Length",$response->getFile()->getSize());
            $response->headers->set('Content-Type', 'video/webm');
            $response->setAutoEtag();
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                end($fileName)
            );
            return $response;

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, "general.500");
        }

    }

    public function streamVideo()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $answer = $this->entityManager->getRepository(Answer::class)->findOneBy(['id' => $inputs['answer_id']]);
            if (!$answer || !$answer->getVideo()) {
                return $this->responseService->getResponseToClient(null, 404, "file.not_exist");
            }

            $fileName = explode("/", $answer->getVideo());
            $filePath = $this->parameterBag->get('video_path_download') . end($fileName);

            if (!file_exists($filePath)) {
                return $this->responseService->getResponseToClient(null, 404, "file.not_exist");
            }

            $response = new BinaryFileResponse($filePath);
            $response->headers->set("Content-Length", (string)$response->getFile()->getSize());
            $response->headers->set('Content-Type', 'video/webm');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                end($fileName)
            );

            return $response;
        } catch (\Exception $exception) {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, "general.500");
        }

    }

    public function setSteps()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $scenario = $this->entityManager->getRepository(Scenario::class)->findOneBy(['id' => $inputs['idstep']]);
            if (!$scenario)
            {
                return $this->responseService->getResponseToClient(null,404,"scenario.not_found");
            }
            $steps = $this->serializer->deserialize($inputs['payloads'], 'App\Entity\Step[]', 'json');
            if (!$steps)
            {
                return $this->responseService->getResponseToClient(null,404,"steps.not_found");
            }
            foreach ($steps as $step) {
                $scenario->addStep($step);
                $scenario->setEtat(1);
                $this->entityManager->persist($scenario);
            }
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient(null,200,"steps.success_add");

        }catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient($exception->getMessage(), 500, "general.500");
        }
    }

}
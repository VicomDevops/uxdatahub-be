<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\QuestionChoices;
use App\Entity\Scenario;
use App\Entity\Step;
use App\Service\ResponseService;
use App\Service\StepsServices;
use App\Service\ValidationErrors;
use App\Utils\ParamsHelper;
use App\Validator\Steps\stepVideoDownloaderValidator;
use App\Validator\Steps\addStepsValidator;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/scenario")
 * @OA\Tag(name="Steps")
 */
class StepController extends AbstractController
{

    private function addStep(Scenario $scenario, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidationErrors $validationErrors)
    {
        $this->denyAccessUnlessGranted('edit', $scenario);

        $step = $serializer->deserialize($request->getContent(), Step::class, 'json');

        $step->setScenario($scenario);

        $errors = $validationErrors->getErrors($step);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($step);
        $entityManager->flush();

        return $this->json(['message' => 'Etape ajoutée avec succès'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/add/steps", name="api_add_steps_to_scenario", methods={"POST"})
     * @OA\RequestBody(
     *     description="Request body for adding steps",
     *     required=true,
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="idstep", type="string", description="ID step"),
     *         @OA\Property(property="payloads", type="string", description="Json payloads for steps"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */

    public function addSteps(Request $request,ParamsHelper $paramsHelper, LoggerInterface $stepLogger, ValidatorInterface $validator, StepsServices $stepsServices, ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(),true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($stepLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new addStepsValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $stepsServices->setSteps();
    }

    /**
     * @Route("/{id}/steps/{step_id}", name="add_step_to_scenario", methods={"DELETE"})
     * @ParamConverter("step", options={"id" = "step_id"})
     * 
     */    

    public function deleteStep(Scenario $scenario, Step $step, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('edit', $scenario);
        $entityManager->remove($step);
        $n = $step->getNumber();
        $steps = $scenario->getSteps();
        foreach ($steps as $s) {
            $num = $s->getNumber();
            if ($num > $n) {
                $s->setNumber($s->getNumber() - 1);
            }
        }
        $entityManager->flush();
    }

    /**
     * @Route("/{id}/steps", name="edit_steps_in_scenario", methods={"PUT"})
     * @OA\RequestBody(
     *     description="Mise à jour des étapes",
     *     @Model(type=Step::class, groups={"create_step"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="202",
     *     description="Mise à jour des étapes avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Mise à jour des étapes échoué",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function editSteps(Scenario $scenario, EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request,ValidationErrors $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', $scenario);

        try {
            foreach ($scenario->getSteps() as $step) {
                $entityManager->remove($step);
            }

            $steps = $serializer->deserialize($request->getContent(), 'App\Entity\Step[]', 'json');
            foreach ($steps as $step) {
                $scenario->addStep($step);
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['message' => 'Etapes modifiées avec succès'], Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/{id}/steps/{step_id}", name="edit_step_in_scenario", methods={"PUT"})
     * @ParamConverter("step", options={"id" = "step_id"})
     * @OA\RequestBody(
     *     description="Mise à jour panel de testeurs",
     *     @Model(type=Step::class, groups={"create_step"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="202",
     *     description="Mise à jour de l'étape avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Mise à jour panel échoué",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function editStep(Scenario $scenario, Step $step, EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request,ValidationErrors $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', $scenario);
        $serializer->deserialize($request->getContent(), Step::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $step]);
        $entityManager->flush();
        return $this->json(['message' => 'Etape modifiée avec succès'], Response::HTTP_ACCEPTED);
    }


    /**
     * @Route("/step/{id}", name="get_step", methods={"get"})

     */
    public function getStep(Step $step, SerializerInterface $serializer)
    {

        $json = $serializer->serialize($step, 'json', ['groups' => 'get_step']);

        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/step/answer/{id}", name="add_comment_to_answer", methods={"PUT"})
     */
    public function addCommentToAnswer(Answer $answer, EntityManagerInterface $entityManager, Request $request)
    {
        $answer->setClientComment(json_decode($request->getContent(), true)['comment']);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire ajouté avec succès'], Response::HTTP_ACCEPTED);    
    }

    /**
     * @Route("/step/video/download", name="api_video_downloader", methods={"GET"})
     * @OA\Parameter(
     *     name="answer_id",
     *     in="query",
     *     description="ID step to download video ",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */

    public function stepVideoDownloader(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, ValidatorInterface $validator, StepsServices $stepsServices, ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new stepVideoDownloaderValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $stepsServices->getVideo();

    }

    /**
     * @Route("/step/video/stream", name="api_video_stream", methods={"GET"})
     * @OA\Parameter(
     *     name="answer_id",
     *     in="query",
     *     description="ID step to download video ",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */

    public function stepVideoStream(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, ValidatorInterface $validator, StepsServices $stepsServices, ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new stepVideoDownloaderValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $stepsServices->streamVideo();

    }
}

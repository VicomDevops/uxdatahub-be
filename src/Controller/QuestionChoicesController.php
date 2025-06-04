<?php

namespace App\Controller;

use App\Entity\QuestionChoices;
use App\Service\ValidationErrors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
/**
 * @Route("/api/question")
 * @OA\Tag(name="questionChoice")
 */
class QuestionChoicesController extends AbstractController
{
    /**
     * @Route("", name="create_questionChoices", methods={"POST"})
     */

    public function createQuestion(Request $request, SerializerInterface $serializer, ValidationErrors $validationErrors, EntityManagerInterface $entityManager)
    {
        /** @var QuestionChoices $question */
        $question = $serializer->deserialize($request->getContent(), QuestionChoices::class, 'json');

        $errors = $validationErrors->getErrors($question);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($question);
        $entityManager->flush();

        return $this->json(['message' => 'Question ajouté avec succès']);
    }

}

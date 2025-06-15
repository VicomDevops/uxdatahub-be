<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Client;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/comment")
 * @IsGranted("ROLE_CLIENT")
 * @OA\Tag(name="Comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/answer/{id}", name="create_comment", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Create a comment related to answer ",
     *     @OA\JsonContent(
     *         type="object",
     *         example="*",
     *     )
     * )
     */
    public function create(Request $request, Answer $answer, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        /** @var Comment $comment */
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $comment->setClient($this->getUser());
        $entityManager->persist($comment);
        $answer->addComment($comment);
        $entityManager->flush();
        return $this->json(['message' => 'commentaire créé avec succès'], 201);
    }

    /**
     * @Route("/answer/{id}", name="update_comment", methods={"PATCH"})
     */
    public function update(Comment $comment ,Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent(), true)['content'];
        $comment->setContent($content);
        $entityManager->flush();

        return $this->json(['message' => 'commentaire modifié avec succès'], 200);
    }

    /**
     * @Route("/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function delete(Comment $comment, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(['message' => 'commentaire supprimé'], 204);
    }

    // /**
    //  * @Route("/answer/{id}", name="get_comment", methods={"GET"})
    //  */
    // public function getComments(Answer $answer,SerializerInterface $serializer){
    //     $comments = $answer->getComments();
    //     $json = $serializer->serialize($comments, 'json', ['groups' => 'client_comment']);

    //     return $this->json($comments, 200);
    // }
}

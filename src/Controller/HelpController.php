<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Help;
use App\Service\ValidationErrors;
use Exception;

/**
 * @Route("api/help")
 * @OA\Tag(name="Help")
 */
class HelpController extends AbstractController
{
    private $request;
    private $entityManager;
    private $serializer;

    public function __construct( EntityManagerInterface $entityManager,SerializerInterface $serializer )
    {
    
        $this->entityManager=$entityManager;
        $this->serializer=$serializer;
    }

     /**
     * @Route("", name="create_help_demand", methods={"POST"})
     * @OA\RequestBody(
     *     description="Créer une demande d'aide",
     *     @Model(type=Help::class, groups={"create_help"}),
     *     required=true
     * )

     */

    public function create(Request $request, SerializerInterface $serializer,ValidationErrors $validationErrors)
    {
        $user = $this->getUser();
            $help = $serializer->deserialize($request->getContent(), Help::class, 'json',['groups' => 'create_help']);
            $errors = $validationErrors->getErrors($help);
        
    
        $help->setLauncher($user);
        $this->entityManager->persist($help);
        $this->entityManager->flush();

        return $this->json(['message' => 'demande d\'aide créé avec succès'], 201);
    }

}
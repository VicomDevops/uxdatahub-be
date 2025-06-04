<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\LicenceCategory;
use App\Repository\LicenceTypeRepository;
use App\Service\ValidationErrors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/licence-types")
 * @OA\Tag(name="LicenceType")
 */
class LicenceTypeController extends AbstractController
{
    /**
     * @Route("", name="get_all_licenceTypes_without_demo", methods={"GET"})
     * @OA\RequestBody(
     *     description="get_all_licenceTypes_without_demo"
     * )
     */
    public function getAll(LicenceTypeRepository $licenceTypeRepository, SerializerInterface $serializer)
    {
        $licenceTypes = $licenceTypeRepository->getNonDemoLicenceTypes();
        $json = $serializer->serialize($licenceTypes, 'json', ['groups' => 'buy_licence']);

        return new Response($json, 200);
    }

    /**
     * @Route("", name="create_licence-type", methods={"POST"})
     * @OA\RequestBody(
     *     description="Create Licence",
     *     @Model(type=LicenceCategory::class, groups={"create_type"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Licence créé avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Problème lors de la création",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function create(SerializerInterface $serializer, Request $request, EntityManagerInterface $entityManager, ValidationErrors $validationErrors,ValidatorInterface $validator)
    {
        $user = $this->getUser();

        if (! $user instanceof Admin) {
            return new Response('Vous n\'avez pas le droit de créer licence Type', Response::HTTP_UNAUTHORIZED);
        }
        $licenceType = $serializer->deserialize($request->getContent(), LicenceCategory::class, 'json',['groups' => 'create_type']);
        $errors = $validationErrors->getErrors($licenceType);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($licenceType);
        $entityManager->flush();

        return $this->json(['message' => 'Type de licence créé'], 200);
    }

    /**
     * @Route("/{id}", name="delete_licence_type", methods={"DELETE"})
     */
    public function delete(LicenceCategory $licenceType, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($licenceType);
        $entityManager->flush();

        return $this->json(['message' => 'Type de licence supprimé avec succès'], 204);
    }

    /**
     * @Route("/{id}", name="edit_licence-type", methods={"PUT"})
     * @OA\RequestBody(
     *     description="Update Licence",
     *     @Model(type=LicenceCategory::class, groups={"edit_type"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Licence modifiée avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Problème lors de l'update",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function update(LicenceCategory $licenceCategory, SerializerInterface $serializer, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', $this->getUser());
        $serializer->deserialize($request->getContent(), LicenceCategory::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $licenceCategory, 'groups' => 'edit_type']);
        $entityManager->flush();
        return $this->json(['message' => 'LicenceType modifié avec succès'], 200);
    }
}

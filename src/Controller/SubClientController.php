<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\SubClient;
use App\Entity\User;
use App\Repository\SubClientRepository;
use App\Service\AdminService;
use App\Service\Mailer;
use App\Service\PasswordGenerator;
use App\Service\ResponseService;
use App\Service\SubClientService;
use App\Service\ValidationErrors;
use App\Utils\ParamsHelper;
use App\Validator\admin\RemovingSubClientValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/subclients")
 * @OA\Tag(name="SubClient")
 */
class SubClientController extends AbstractController
{
    /**
     * @Route("", name="create_subclient", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, PasswordGenerator $passwordGenerator, ValidationErrors $validationErrors)
    {
        try {
            $client = $this->getUser();
            $subclient = $serializer->deserialize($request->getContent(), SubClient::class, 'json');

            $subclient->setClient($client)
                ->setIsActive(true)
                ->setRoles(['ROLE_SUBCLIENT']);

            $errors = $validationErrors->getErrors($subclient);
            if (count($errors) > 0) {
                return $this->json([
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $password = $passwordGenerator->newPassword($subclient);

        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json(['message' => 'Compte créé avec succès']);
    }

    /**
     * @Route("/remove", name="delete_subclient", methods={"GET"})
     * @OA\Parameter(
     *     name="subclient_id",
     *     in="query",
     *     description="ID of the subclient",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns ok if the admin removed",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function removingAdmin(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, SubClientService $subClientService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new RemovingSubClientValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $subClientService->removeSubClient();
    }

    /**
     * @Route("", name="get_sub_clients", methods={"GET"})
     */
    public function getSubClients(SubClientRepository $subClientRepository, SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $subclients = $subClientRepository->getAllByClient($this->getUser());
        $json = $serializer->serialize($subclients, 'json');

        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/new-password", name="new_password", methods={"GET"})
     */
    public function regeneratePassword(User $user, PasswordGenerator $passwordGenerator, Mailer $mailer)
    {
        $password = $passwordGenerator->newPassword($user);
        $mailer->sendPassword($user, $password);

        return $this->json(['message' => 'mot de passe modifié'], 200);
    }

    /**
     * @Route("/{id}/desactivate", name="desactivate_sub_client", methods={"GET"})
     */
    public function desactivate(SubClient $subClient, EntityManagerInterface $entityManager)
    {
        $subClient->setIsActive(false);
        $entityManager->flush();

        return $this->json(['message' => 'Compte désactivé avec succès'], 200);
    }

    /**
     * @Route("/{id}/activate", name="activate_sub_client", methods={"GET"})
     */
    public function activate(SubClient $subClient, EntityManagerInterface $entityManager)
    {
        $subClient->setIsActive(true);
        $entityManager->flush();

        return $this->json(['message' => 'Compte activé avec succès'], 200);
    }

    /**
     * @Route("/{id}/read-rights", name="read_rights_sub_client", methods={"GET"})
     */
    public function readRights(SubClient $subClient, EntityManagerInterface $entityManager)
    {
        $subClient->setWriteRights(false);
        $entityManager->flush();

        return $this->json(['message' => 'Droits de lecture accordé'], 200);
    }

    /**
     * @Route("/{id}/write-rights", name="write_rights_sub_client", methods={"GET"})
     */
    public function writeRights(SubClient $subClient, EntityManagerInterface $entityManager)
    {
        $subClient->setWriteRights(true);
        $entityManager->flush();

        return $this->json(['message' => 'Droits d\'écriture accordé'], 200);
    }
}

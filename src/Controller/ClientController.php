<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\CommentRepository;
use App\Repository\LicenceTypeRepository;
use App\Service\AdminService;
use App\Service\ClientService;
use App\Service\LicenceService;
use App\Service\Mailer;
use App\Service\PasswordGenerator;
use App\Service\ResponseService;
use App\Service\UploadVideo;
use App\Utils\ParamsHelper;
use App\Validator\Steps\AnalyzeByStepValidator;
use App\Validator\admin\clientValidationValidator;
use App\Validator\Client\confirmClientAccountValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/clients")
 * @OA\Tag(name="Client")
 */
class ClientController extends AbstractController
{
    private $clientRepository;
    private $serializer;
    private $entityManager;
    private $mailer;

    public function __construct(ClientRepository $clientRepository, SerializerInterface $serializer, Mailer $mailer, EntityManagerInterface $entityManager)
    {
        $this->clientRepository = $clientRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/validate/client", name="api_validate_client_", methods={"GET"})
     * @OA\Parameter(
     *     name="client_id",
     *     in="query",
     *     description="id client",
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
    public function clientValidation(Request $request,ParamsHelper $paramsHelper, LoggerInterface $clientLogger, AdminService $adminService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new clientValidationValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $adminService->validateClient();
    }

    /**
     * @Route("/confirm/client/account", name="api_validate_client_account_email", methods={"GET"})
     * @OA\Parameter(
     *     name="token",
     *     in="query",
     *     description="client client",
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
    public function confirmClientAccount(Request $request,ParamsHelper $paramsHelper, LoggerInterface $clientLogger, ClientService $clientService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new confirmClientAccountValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $clientService->setClientAccountToConfirmed();
    }

    /**
     * @Route("/list", name="api_get_new_clients", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns the list of new clients",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     */
    public function getNewClients(Request $request,ParamsHelper $paramsHelper, LoggerInterface $clientLogger, AdminService $adminService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientLogger);
        $paramsHelper->flushInputWithLogger();

        return $adminService->getNewClientsList();
    }



    /**
     * @Route("/{id}/comments", name="get_client_comment", methods={"GET"})
     */
    public function getCommentsByClient(CommentRepository $commentRepository, Client $client, SerializerInterface $serializer)
    {
        $comments = $commentRepository->findBy(['client' => $client]);
        $json = $serializer->serialize($comments, 'json', ['groups' => 'client_comment']);
        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/compteurs", name="get_client_compteurs", methods={"GET"})
     */
    public function getCompteursByClient(SerializerInterface $serializer)
    {
        $compteurs = [];
        $client = $this->getUser();
        if ($client instanceof Client) {
            $subClient = $client->getSubClients();
            $comptesactifs = count($subClient);
            $compteurs['comptes_actifs'] = $comptesactifs + 1;
            $scenarios = count($client->getScenarios());
            $compteurs['scenarios'] = $scenarios;
            $count=count($client->getContracts());
            $compteurs['licence'] = ($count!= 0) ? $client->getContracts()[$count-1]->getLicenceCategory()->getTitle() : null;
            $json = $serializer->serialize($compteurs, 'json');
            return new Response($json, Response::HTTP_OK);
        } else
            return new Response('Vous n\'avez pas un client', Response::HTTP_UNAUTHORIZED);
    }
    /**
     * @Route("/demande_modif_contract_sepa", name="demande_modif_contract_sepa", methods={"GET"})
     * @OA\RequestBody(
     *     description="Demande modif contract ou sepa info client"
     * )
     * @OA\Response(
     *     response="201",
     *     description="EMAIL envoyé à l'admin avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Envoi d'Email échoué",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function demandeModifOrSuppContratOrSepa()
    {
        $client = $this->getUser();
        try {
            $this->mailer->sendClientDemandeNotification($client);
            return $this->json(['message' => 'L\'admin est bien notifié et va vous contactez le plutot possible'], 200);
        } catch (\Exception $e) {
            return $this->json(
                [
                    "message" => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * @Route("/update", name="update_client", methods={"PUT"})
     * @OA\RequestBody(
     *     description="Update Client",
     *     @Model(type=Client::class, groups={"update_client"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Profile modifié avec succès",
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
    public function updateClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager,UploadVideo $uploadService)
    {
        $client=$this->getUser();
        try{
            $serializer->deserialize($request->getContent(), Client::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $client]);
            if (isset($_FILES["profileImage"])) {
                $profileImage=$uploadService->uploadProfileImage($_FILES["profileImage"],$client);
                $client->setProfileImage($profileImage);
            }
            $entityManager->flush();
            return $this->json(['message' => 'Client modifié avec succés'], Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }
}

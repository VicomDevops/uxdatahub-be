<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Contract;
use App\Entity\LicenceCategory;
use App\Message\ClientSepaMessage;
use App\Repository\ContractRepository;
use App\Service\LicenceService;
use App\Service\Mailer;
use App\Service\PaiementService;
use App\Service\UploadVideo;
use App\Service\ValidationErrors;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @Route("/api/contract")
 * @OA\Tag(name="Contract")
 */
class ContractController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="get_contract", methods={"GET"})
     * @isGranted("ROLE_ADMIN")
     */

    public function getContracts(ContractRepository $contractRepository,SerializerInterface $serializer){
        $contracts= $contractRepository->findAll();
        $json=$serializer->serialize($contracts,'json',['groups'=> 'view_contract']);
        return new Response($json,Response::HTTP_OK);
    }

    /**
     * @Route("/send_contract_infos/{id}", name="app_contract",methods={"POST"})
     * @isGranted("ROLE_CLIENT")
    
     * @OA\RequestBody(
     *     description="Créer un Contrat",
     *     @Model(type=Contract::class, groups={"create_contract"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="201",
     *     description="Création de contrat avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Création du contrat échouée",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function create(LicenceCategory $licenceCategory, Request $request, SerializerInterface $serializer,ValidationErrors $validationErrors,UploadVideo $uploadService)
    {
        $contract = $serializer->deserialize(json_encode($_REQUEST), Contract::class, 'json',['groups' => 'create_contract']);
        $errors = $validationErrors->getErrors($contract);
        
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $client=$this->getUser();
        $contract->setClient($client);
        $contract->setStatus(0);
        $contract->setLicenceCategory($licenceCategory);
        $identityCardFront = $uploadService->uploadIdentityCard($_FILES["identityCardFront"],$client);
        $identityCardBack = $uploadService->uploadIdentityCard($_FILES["identityCardBack"],$client,'back');
        if ($identityCardFront & $identityCardBack) {
            $contract->setidentityCardFront($identityCardFront);
            $contract->setidentityCardBack($identityCardBack);
        }else{
            return new Response('error on adding identity Card', 400);
        }

        $this->entityManager->persist($contract);
        $this->entityManager->flush();
        return $this->json(['message'=>'Contrat infos envoyés avec succès','contract_id'=>$contract->getId()],201);
    }


    /**
     * @Route("/notify_admin", name="notify_admin",methods={"GET"})
     * isGranted("ROLE_CLIENT")
    
     * @OA\RequestBody(
     *     description="Notifier l' administrateur pour créer un contrat DOCUSIGN"
     * )
     * @OA\Response(
     *     response="201",
     *     description="EMAIL envoyé avec succès",
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

    public function NotifyAdmin(Mailer $mailer){
        $client=$this->getUser();
        try{
            $mailer->sendAdminEmailNotification($client);
            return $this->json(['message'=>'Email envoyé avec succès'],201);
        }catch (\Exception $e) {
            return $this->json([
                "message" => $e->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }
      
    }


     /**
     * @Route("/notify_client/{id}", name="notify_client",methods={"GET"})
     * isGranted("ROLE_ADMIN")
    
     * @OA\RequestBody(
     *     description="Notifier le client que son contrat DOCUSIGN  est pret à signer"
     * )
     * @OA\Response(
     *     response="201",
     *     description="EMAIL envoyé avec succès",
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

    public function NotifyClient(Mailer $mailer,Client $client){
        try{
            $mailer->sendClientEmailNotification($client);
            $count=count($client->getContracts());
            $client->getContracts()[$count-1]->setStatus(1);
            $this->entityManager->flush();
            return $this->json(['message'=>'Email envoyé avec succès au Client'],201);
        }catch (\Exception $e) {
            return $this->json([
                "message" => $e->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }
      
    }

     /**
     * @Route("/upload_client_contract/{id}", name="upload_client_contract",methods={"POST"})
     * isGranted("ROLE_ADMIN")
     * @OA\RequestBody(
     *     description="upload le contrat client sur la platforme"
     * )
     * @OA\Response(
     *     response="201",
     *     description="Contrat uploaded avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Upload du contrat échoué",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function UploadClientContract(Client $client, UploadVideo $uploadService, MessageBusInterface $bus, PaiementService $paiementService)
    {

        try{
        $contract = $_FILES['contract'];
        $videoPath = $uploadService->uploadContractFile($contract, $client);
        if (!$videoPath) {
            return new Response('error on adding contract', 400);
        }
        $client->setContractLink($videoPath);
        $count=count($client->getContracts());
        $client->getContracts()[$count-1]->setStatus(2);
        $client->getContracts()[$count-1]->setStartAt(new \DateTime('now'));
        $this->entityManager->flush();
        $p=$paiementService->sepaPaiement($client);
        dd($p);
        // $bus->dispatch(new ClientSepaMessage($client->getId()));
        return new Response("contract uploaded pour le client ".$client->getId(), Response::HTTP_OK);

        }catch(\Exception $e ){
            return new Response($e->getMessage(), 400);
        }
        
    }

         /**
     * @Route("/resiliation_contrat/{id}", name="resiliation_client_contract",methods={"GET"})
     * isGranted("ROLE_ADMIN")
     * @OA\RequestBody(
     *     description="resiliation du contrat"
     * )
     * @OA\Response(
     *     response="201",
     *     description="Résiliation faite avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Résiliation échouée",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function resiliationContrat(Client $client,EntityManagerInterface $entityManager)
    {

        try{
            $count=count($client->getContracts());
            $contractClient=$client->getContracts()[$count-1];
            $dateStart=$contractClient->getStartAt()->format("d");
            if(date("d")<= $dateStart){
                $month=date("m");
            }else{
                $month=date("m")+1;
            }
            $dateResiliation=new DateTime(date("Y").'-'.$month.'-'.$dateStart);
            $contractClient->setEndAt($dateResiliation);
            $entityManager->flush();
           

        return new Response("résiliation effectuée avec succés", Response::HTTP_OK);

        }catch(\Exception $e ){
            return new Response($e->getMessage(), 400);
        }
        
    }

     /**
     * @Route("/send_sepa_infos/{id}", name="info_sepa",methods={"PUT"})
     * @isGranted("ROLE_CLIENT")ù
     * @OA\RequestBody(
     *     description="Ajouter sepa infos à un Contrat",
     *     @Model(type=Contract::class, groups={"create_sepa_infos"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="201",
     *     description="update de contrat avec succès",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     * @OA\Response(
     *     response="400",
     *     description="update du contrat échouée",
     *     @OA\JsonContent(
     *     type="string",
     *     example="message"
     * )
     * )
     */
    public function update(Contract $contract, Request $request, SerializerInterface $serializer,ValidationErrors $validationErrors, PaiementService $paiementService)
    {
        $serializer->deserialize($request->getContent(), Contract::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $contract,'groups' => 'create_sepa_infos']);
        $errors = $validationErrors->getErrors($contract);
        
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $this->entityManager->flush();
        return $this->json(['message'=>'sepa infos envoyés avec succès'],201);
    }
}

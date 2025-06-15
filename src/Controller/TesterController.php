<?php

namespace App\Controller;

use App\Entity\ClientTester;
use App\Entity\Panel;
use App\Entity\Tester;
use App\Repository\ScenarioRepository;
use App\Repository\TesterRepository;
use App\Repository\TestRepository;
use App\Service\Mailer;
use App\Service\PasswordGenerator;
use App\Service\ResponseService;
use App\Service\StripeClient;
use App\Service\TestersService;
use App\Service\UploadVideo;
use App\Utils\ParamsHelper;
use App\Validator\admin\clientValidationValidator;
use App\Validator\admin\testerValidationValidator;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/testers")
 * @OA\Tag(name="Testeur")
 */
class TesterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="get_testers", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @OA\RequestBody(
     *     description="Get new testers"
     * )
     * @OA\Response(
     *     response="200",
     *     description="liste des nouveaux testeurs"
     * )
     * @OA\Response(
     *     response="400",
     *     description="Erreur/Exception"
     * )
     */
    public function getNewTesters(TesterRepository $testerRepository, SerializerInterface $serializer)
    {
        try{
            $testers = $testerRepository->getNewTesters();
            $json = $serializer->serialize($testers, 'json');
            return new Response($json, Response::HTTP_OK);
        }catch(\Exception $e ){
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * @Route("/{id}", name="get_tester_infos", methods={"GET"})
     */
    public function TesterInfos(Tester $tester, TesterRepository $testerRepository, SerializerInterface $serializer)
    {
        $tests = $tester->getTests();
        $json = $serializer->serialize($tests, 'json', ['groups' => 'get_test']);
        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/validate", name="api_validate_tester", methods={"GET"})
     * @OA\Parameter(
     *     name="tester_id",
     *     in="query",
     *     description="ID of the tester",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns ok if the tester is validated",
     *     @OA\JsonContent(
     *        type="string",
     *        example="*"
     *     )
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function testerValidation(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger,TestersService $testersService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new testerValidationValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $testersService->valdiateTester();
    }

    /**
     * @Route("/{id}/iban", name="stripe_tester", methods={"PUT"})
     */
    public function stripeTester(Request $request, Tester $tester, StripeClient $stripeClient)
    {
        $content = json_decode($request->getContent(), true);
        $stripeToken = $content['stripeToken'];
        $customer = $stripeClient->createCustomer($tester, $stripeToken);

        return $this->json(['message' => 'IBAN ajouté avec succès'], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="update_tester", methods={"PUT"})
     * @OA\RequestBody(
     *     description="Update Tester",
     *     @Model(type=Tester::class, groups={"update_tester"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Profil modifié avec succès",
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
    public function updateTester(Request $request, Tester $tester, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        try {
            $serializer->deserialize($request->getContent(), Tester::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $tester]);
            $entityManager->flush();
            return $this->json(['message' => 'Testeur modifié avec succés'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/tester/compteur", name="get_tester_compteurs", methods={"GET"})
     */
    public function getCompteursByTester(SerializerInterface $serializer, ScenarioRepository $scenarioRepository, TestRepository $testRepository)
    {
        $compteurs = [];
        $user = $this->getUser();

        if ($user instanceof ClientTester) {
            $scenariosToBeTested = $scenarioRepository->findScenariosByClientTester($user);
            $scenariosClosed = $scenarioRepository->findScenariosClosedByClientTester($user);
        } elseif ($user instanceof Tester) {
            $scenariosToBeTested = $scenarioRepository->findScenariosByTester($user);
            $scenariosClosed = $scenarioRepository->findScenariosClosedByTester($user);
        } else
            return new Response('Vous n\'etes pas un testeur', Response::HTTP_UNAUTHORIZED);
        $etat = [];
        $scenarioIds = [];
        foreach ($scenariosToBeTested as $scenario) {
            $scenarioIds[] = $scenario->getId();
        }
        if ($user instanceof ClientTester) {
            $tests = $testRepository->findBy(['clientTester' => $user, 'scenario' => $scenarioIds]);
        } elseif ($user instanceof Tester) {
            $tests = $testRepository->findBy(['tester' => $user, 'scenario' => $scenarioIds]);
        }
        foreach($tests as $key=>$test){
            if(isset($test)){
                $etat[$key]=$test->getEtat();
            }else
            {
                $etat[$key]=null;
            }
        }
        $sommeScenariosATester = count(array_keys($etat, 0, true)) + count(array_keys($etat, 1, true)) + count(array_keys($etat, null, true));
        $compteurs['scenariosATester'] = $sommeScenariosATester;
        $compteurs['scenarioTermines'] = count(array_keys($etat, 2, true));
        $compteurs['scenariosClosed'] = count($scenariosClosed);
        $json = $serializer->serialize($compteurs, 'json');
        return new Response($json, Response::HTTP_OK);
    }

    /**
     * @Route("/profile-image/{id}", name="update_profile_image_tester", methods={"POST"})
     * @OA\RequestBody(
     *     description="Update Profile Image Tester",
     *     @Model(type=Tester::class, groups={"profile_image_tester"}),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Profil modifié avec succès",
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
    public function updateProfileImageTester(Tester $tester, EntityManagerInterface $entityManager, UploadVideo $uploadService)
    {
        try{
            $file=$_FILES["profileImage"];
            $profileImage=$uploadService->uploadProfileImage($file,$tester);
            $tester->setProfileImage($profileImage);
            $entityManager->flush();
            return $this->json(['message' => 'Tester modifié avec succés'], Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

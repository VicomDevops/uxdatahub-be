<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\PasswordGenerator;
use App\Service\ResponseService;
use App\Service\ValidationErrors;
use App\Service\AdminService;
use App\Utils\ParamsHelper;
use App\Validator\admin\clientValidationValidator;
use App\Validator\admin\RemovingAdminValidator;
use App\Validator\admin\ResetScenariosValidator;
use App\Validator\admin\reanalyzeTestValidator;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/admins")
 * @OA\Tag(name="Admin")
 */
class AdminController extends AbstractController
{

    /**
     * @Route("", name="get_all_admins", methods={"GET"})
     */
    public function getAllAdmins(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, AdminService $adminService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();

        return $adminService->getListAdmins();
    }

    /**
     * @Route("/reset/tester/scenarios", name="api_reset_testers_scenarios_by_admin", methods={"POST"})
     * @OA\RequestBody(
     *      request="clientTesterData",
     *      required=true,
     *      description="JSON payload for unpassed scenarios client tester",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="tester_id", type="integer", example=1),
     *          @OA\Property(property="scenario_id", type="integer", example=1)
     *      )
     *  )
     * @OA\Response(
     *      response=200,
     *      description="Returns 200 if the scenarios of the testers are resetted",
     *      @OA\JsonContent(
     *          type="string",
     *          example="*"
     *      )
     *  )
     */
    public function resetScenarios(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, AdminService $adminService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ResetScenariosValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $adminService->resetScenariosForTesters();
    }

    /**
     * @Route("/reanalyze/test", name="api_reanalyze_test_by_admin", methods={"POST"})
     * @OA\RequestBody(
     *      request="Test ID",
     *      required=true,
     *      description="JSON payload to reanalyze existing test",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="test_id", type="integer", example=1),
     *      )
     *  )
     * @OA\Response(
     *      response=200,
     *      description="Returns 200 if the scenarios of the testers are resetted",
     *      @OA\JsonContent(
     *          type="string",
     *          example="*"
     *      )
     *  )
     */
    public function reanalyzeTest(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, AdminService $adminService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new reanalyzeTestValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $adminService->submitReanalyzeTest();
    }

    /**
     * @Route("", name="create_admin", methods={"POST"})
     */
    public function createAdmin(Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder,PasswordGenerator $passwordGenerator,EntityManagerInterface $entityManager, ValidationErrors $validationErrors, Mailer $mailer)
    {
        try {

            /** @var Admin $admin */
            $admin = $serializer->deserialize($request->getContent(), Admin::class, 'json');
            $errors = $validationErrors->getErrors($admin);

            if (count($errors) > 0) {
                return $this->json([
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }
            $password = $passwordGenerator->generatePassword(10);

            $admin->setRoles(['ROLE_ADMIN'])
                ->setPassword($passwordEncoder->encodePassword(
                    $admin,
                    $password
                ))
                ->setIsActive(true);
            $entityManager->persist($admin);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(["message" => " Ce mail est déjà utilisé "], Response::HTTP_CONFLICT);
        }

        $mailer->sendPassword($admin, $password);
        return $this->json(["message" => "Admin ajouté avec succès."], Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}/new-password", name="new_password", methods={"GET"})
     */
    public function regeneratePassword(User $user, PasswordGenerator $passwordGenerator, Mailer $mailer)
    {
        $password = $passwordGenerator->newPassword($user);
        $mailer->sendPassword($user, $password);

        return $this->json(['message' => 'Nouveau mot de passe généré'], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/desactivate-user", name="desactivate", methods={"GET"})
     */
    public function desactivateUser(User $user, EntityManagerInterface $entityManager)
    {
        if (!$user) {
            return $this->json(['message' => "Cet utilisateur n'existe pas"], Response::HTTP_BAD_REQUEST);
        }

        $user->setIsActive(false);
        $entityManager->flush();

        return $this->json(['message' => "Utilisateur désactivé"], Response::HTTP_OK);
    }

    /**
     * @Route("/remove", name="api_delete_admin", methods={"GET"})
     * @OA\Parameter(
     *     name="admin_id",
     *     in="query",
     *     description="ID of the admin",
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
     * @IsGranted("ROLE_ADMIN")
     */
    public function removingAdmin(Request $request,ParamsHelper $paramsHelper, LoggerInterface $adminLogger, AdminService $adminService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($adminLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new RemovingAdminValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $adminService->removeAdmin();
    }
    

     /**
     *@Route("/user", name="user_details", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    
    public function userDetails(Request $request,UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $request_content=$request->getContent();
        $id=json_decode($request_content,true)['id'];
        $user = $userRepository->findOneBy(['id' => $id]);
        dd($user);
    }

    /**
     * @Route("/user/{id}", name="update_user", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateUser(User $user, SerializerInterface $serializer, EntityManagerInterface $entityManager,Request $request)
    {
        try{
            $serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
            $entityManager->flush();
            return $this->json(['message' => 'User modifié avec succés'], Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->json(['message' => 'Erreur'], Response::HTTP_BAD_REQUEST);
        }

    }
}

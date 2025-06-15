<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ClientTesterProfileService;
use App\Service\Mailer;
use App\Service\PasswordGenerator;
use App\Service\PasswordService;
use App\Service\ResponseService;
use App\Service\UserService;
use App\Utils\ParamsHelper;
use App\Validator\User\UserChangePasswordValidator;
use App\Validator\User\UserVerifyEmailValidator;
use App\Validator\Profile\ClientTesterCompleteProfileValidator;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/api")
 * @OA\Tag(name="User")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/current/user", name="api_get_current_user", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns the current user",
     *     @OA\JsonContent(
     *         type="JSON"
     *     )
     * )
     */
    public function currentUser(ValidatorInterface $validator,UserService $userService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $userLogger,ResponseService $responseService)
    {
        $inputs = $request->query->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($userLogger);
        $paramsHelper->flushInputWithLogger();

        return $userService->getCurrentUser();
    }
    /**
     * @Route("/user/{id}/toggleActivate", name="toggle_is_active", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function toggleActivate(User $user, EntityManagerInterface $entityManager)
    {
        if (!$user) {
            return $this->json(['message' => "Cet utilisateur n'existe pas"], Response::HTTP_BAD_REQUEST);
        }
        $user->setIsActive(!$user->getIsActive());
        $entityManager->flush();

        $message = "Utilisateur désactivé";
        if ($user->getIsActive()) {
            $message = "Utilisateur activé";
        }

        return $this->json(['message' => $message], Response::HTTP_OK);
    }

    /**
     * @Route("/user/{id}/firstConnection", name="change_first_connection", methods={"GET"})
     */
    public function userFirstConnection(User $user, EntityManagerInterface $entityManager)
    {
        $user->setIsFirstConnection(false);
        $entityManager->flush();

        return $this->json(['message' => ""], Response::HTTP_OK);
    }

    /**
     * @Route("/verify/user/email", name="api_verify_user_email", methods={"POST"})
     * @OA\RequestBody(
     *     request="UserData",
     *     required=true,
     *     description="JSON payload for verifying client password",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="email", type="string", example="email"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="{'email':'example@ex.com'}"
     *     )
     * )
     */

    public function verifyUserEmail(ValidatorInterface $validator,UserService $userService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $userLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($userLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new UserVerifyEmailValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $userService->verifyUserEmailProfile();
    }

    /**
     * @Route("/change/user/password", name="api_change_user_password", methods={"POST"})
     * @OA\RequestBody(
     *     request="UserData",
     *     required=true,
     *     description="JSON payload for changing client password",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="old_password", type="string", example="old_pass"),
     *         @OA\Property(property="new_password", type="string", example="new_pass"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="{'old_password':'********','new_password':'********'}"
     *     )
     * )
     */

    public function changeUserPassword(ValidatorInterface $validator,UserService $userService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $userLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($userLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new UserChangePasswordValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $userService->changePasswordValidation();
    }

    /**
     * @Route("/delete_user/email", name="delete_user")
     */
    public function deleteUser( Request $request,SerializerInterface $serializer,UserRepository $userRepository,EntityManagerInterface $entityManager)
    {
        $request_content=$request->getContent();
        $email=json_decode($request_content,true)['email'];
        $user = $userRepository->findOneBy(['email' => $email]);
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->json([
            "message" => "suppression effectuée"
        ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/user/exist", name="user_exist", methods={"POST"})
     * @OA\Parameter(
     *     name="params",
     *     in="query",
     *     @OA\Schema(type="object", required={"email"},
     *          @OA\Property(type="string", property="email"),
     *      )
     *    )
     * )
     * @OA\Response(
     *     response="200",
     *     description="user is existing and mail is sent to change passowrd"
     * )
     * @OA\Response(
     *     response="400",
     *     description="User not found."
     * )
     */
    public function existUser(Request $request,UserRepository $userRepository, Mailer $mailer)
    {
        $request_content=$request->getContent();
        $email=json_decode($request_content,true)['email'];
        $user = $userRepository->findOneBy(['email' => $email]);
        if(isset($user)){
            $mailer->sendForgottenPasswordMail($user);
            return $this->json([
                "message" => "user is existing"
            ],
                Response::HTTP_OK
            );
        }else {

            return $this->json([
                "message" => "utilisateur introuvable"
            ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @Route("/user/new_password", name="user_new_password", methods={"POST"})
     * @OA\Parameter(
     *     name="params",
     *     in="query",
     *     @OA\Schema(type="object", required={"new_password","email"},
     *          @OA\Property(type="string", property="new_password"),
     *          @OA\Property(type="string", property="email")
     *      )
     *    )
     * )
     * @OA\Response(
     *     response="200",
     *     description="password updated"
     * )
     * @OA\Response(
     *     response="400",
     *     description="Empty password."
     * )
     */
    public function user_new_password(ResponseService $responseService,Request $request, PasswordGenerator $passwordGenerator,UserRepository $userRepository,Mailer $mailer,PasswordService $passwordService)
    {
        $email = json_decode($request->getContent(),true)['email'];
        $user= $userRepository->findOneBy(['email'=>$email]);
        if ($user) {
            $response = $passwordService->generateUrl($user);
            $data = json_decode($response->getContent(), true);
            if ($data["header"]["code"] != 200)
            {
                return $responseService->getResponseToClient(null, 201, "general.exception");
            }
            $mailer->sendPassword($user,$data["response"]);
            return $responseService->getResponseToClient(null, 200, "email.success");

        } else {
            return $responseService->getResponseToClient(null, 404, "email.fail");
        }
    }

    /**
     * @Route("/users/all", name="users_get_all", methods={"GET"})
     */
    public function getAll(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $users = $userRepository->findAll();

        $json = $serializer->serialize($users, 'json', ['groups' => 'get_all']);
        return new Response($json, Response::HTTP_OK);
    }
}

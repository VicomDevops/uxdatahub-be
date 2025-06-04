<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\ClientTester;
use App\Entity\Tester;
use App\Repository\ClientTesterRepository;
use App\Service\ClientTesterProfileService;
use App\Service\ResponseService;
use App\Service\UploadVideo;
use App\Utils\ParamsHelper;
use App\Validator\Profile\ClientTesterUpdateProfileValidator;
use App\Validator\Profile\ClientTesterCompleteProfileValidator;
use App\Validator\Profile\UpdateProfilePicValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/clientTester")
 * @OA\Tag(name="ClientTester")
 */
class ClientTesterController extends AbstractController
{
    /**
     * @Route("/submitted", name="client_tester_informations_submitted", methods={"GET"})
     */
    public function clientTesterInformationSubmitted(Request $request,ClientTesterRepository $clientTesterRepository){
        $user = $this->getUser();

        if ($user instanceof ClientTester) {
            if(is_null($user->getGender())){
                return new Response(0, Response::HTTP_OK);
            }else{
                return new Response(1, Response::HTTP_OK);
            }
        }else{
            return new Response('Vous n\'avez pas l\'acces', Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Route("/complete/profile", name="api_additional_client_tester_data", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for updating client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="gender", type="string", example="Femme"),
     *         @OA\Property(property="csp", type="string", example="Ouvriers"),
     *         @OA\Property(property="postalCode", type="string", example="123456"),
     *         @OA\Property(property="city", type="string", example="city"),
     *         @OA\Property(property="adresse", type="string", example="adresse"),
     *         @OA\Property(property="dateOfBirth", type="string", example="2024-02-02"),
     *         @OA\Property(property="phone", type="string", example="+3659215"),
     *         @OA\Property(property="country", type="string", example="Antilles néerlandaises"),
     *         @OA\Property(property="id", type="integer", example=350),
     *         @OA\Property(property="os", type="string", example="MacOS"),
     *         @OA\Property(property="osMobile", type="string", example="Android"),
     *         @OA\Property(property="osTablet", type="string", example="Autre")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="{'gender':'Femme','csp':'Ouvriers','dateOfBirth':'2024-02-02','phone':'+3659215','country':'Antilles néerlandaises','id':350,'os':'MacOS','osMobile':'Android','osTablet':'Autre'}"
     *     )
     * )
     */

    public function completeClientTesterProfile(ValidatorInterface $validator,ClientTesterProfileService $clientTesterProfileService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $clientTesterLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientTesterLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ClientTesterCompleteProfileValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $clientTesterProfileService->completeProfile();
    }

    /**
     * @Route("/update", name="api_update_client_tester_data", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for updating client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="name", type="string", example="name"),
     *         @OA\Property(property="email", type="string", example="example@mail.com"),
     *         @OA\Property(property="city", type="string", example="NY"),
     *         @OA\Property(property="phone", type="string", example="+3659215"),
     *         @OA\Property(property="lastname", type="string", example="lastname")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="{'name':'name','email':'example@mail.com','city':'NY','phone':'+3659215','country':'Australied','lastname':'lastname'}"
     *     )
     * )
     */

    public function updateClientTesterProfile(ValidatorInterface $validator,ClientTesterProfileService $clientTesterProfileService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $clientTesterLogger,ResponseService $responseService)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientTesterLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ClientTesterUpdateProfileValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $clientTesterProfileService->updateProfile();
    }

    /**
     * @Route("/profile/photo", name="api_update_photo_client_tester_data", methods={"POST"})
     * @OA\RequestBody(
     *     request="clientTesterData",
     *     required=true,
     *     description="JSON payload for updating client tester",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="img", type="string", example="image.jpg"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the average and deviation of steps for a given scenario",
     *     @OA\JsonContent(
     *         type="string",
     *         example="{'img':'image.jpg'}"
     *     )
     * )
     */

    public function updateClientTesterProfilePhoto(ValidatorInterface $validator,ClientTesterProfileService $clientTesterProfileService, Request $request,ParamsHelper $paramsHelper,LoggerInterface $clientTesterLogger,ResponseService $responseService)
    {
        $inputs = $request->files->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientTesterLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new UpdateProfilePicValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $clientTesterProfileService->updateProfilePhoto();
    }
}
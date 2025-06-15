<?php

namespace App\Controller;

use App\Service\ClientService;
use App\Service\ResponseService;
use App\Service\TestersService;
use App\Utils\ParamsHelper;
use App\Validator\admin\clientRegistrationValidator;
use App\Validator\Tester\RegisterTesterFormValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 * @OA\Tag(name="Register")
 */
class RegisterController extends AbstractController
{
    /**
     * @Route("/signup-client", name="api_signup_client", methods={"POST"})
     * @OA\RequestBody(
     *     description="Inscription client",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="name", type="string", example="Said"),
     *         @OA\Property(property="lastname", type="string", example="Ben Hmed"),
     *         @OA\Property(property="useCase", type="string", example="Entreprise: Projet Ponctuel"),
     *         @OA\Property(property="nbEmployees", type="string", example="1-10"),
     *         @OA\Property(property="sector", type="string", example="it"),
     *         @OA\Property(property="profession", type="string", example="it"),
     *         @OA\Property(property="email", type="string", example="s.benhmed@labsoftn.fr"),
     *         @OA\Property(property="phone", type="string", example="33555555555"),
     *         @OA\Property(property="company", type="string", example="labsoft"),
     *         @OA\Property(property="cgu", type="boolean", example=true),
     *         @OA\Property(property="privacyPolicy", type="boolean", example=true)
     *     ),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Inscription client avec succès",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="message", type="string", example="Client registered successfully")
     *     )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Inscription client échouée",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="message", type="string", example="Validation failed")
     *     )
     * )
     */
    public function clientRegistration(Request $request,ParamsHelper $paramsHelper, LoggerInterface $clientLogger, ClientService $clientService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = json_decode($request->getContent(), true);
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($clientLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new clientRegistrationValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $clientService->SignUpClients();
    }

    /**
     * @Route("/signup-tester", name="api_signup_tester", methods={"POST"})
     * @OA\RequestBody(
     *     description="Inscription Testeur",
     *     @OA\JsonContent(
     *         type="object",
     *         required={
     *             "name", "lastname", "gender", "country", "ville", "csp", "studyLevel",
     *             "maritalStatus", "adressePostal", "codePostal", "socialMedia", "email",
     *             "dateOfBirth", "phone", "cgu", "privacyPolicy", "identityCardFront",
     *             "identityCardBack"
     *         },
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="lastname", type="string"),
     *         @OA\Property(property="gender", type="string"),
     *         @OA\Property(property="country", type="string"),
     *         @OA\Property(property="ville", type="string"),
     *         @OA\Property(property="csp", type="string"),
     *         @OA\Property(property="studyLevel", type="string"),
     *         @OA\Property(property="maritalStatus", type="string"),
     *         @OA\Property(property="adressePostal", type="string"),
     *         @OA\Property(property="codePostal", type="string"),
     *         @OA\Property(property="socialMedia", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="dateOfBirth", type="string", format="date"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="cgu", type="boolean"),
     *         @OA\Property(property="privacyPolicy", type="boolean"),
     *         @OA\Property(property="identityCardFront", type="string"),
     *         @OA\Property(property="identityCardBack", type="string")
     *     ),
     *     required=true
     * )
     * @OA\Response(
     *     response="200",
     *     description="Inscription testeur avec succès",
     *     @OA\JsonContent(
     *         type="string",
     *         example="message"
     *     )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Inscription testeur échouée",
     *     @OA\JsonContent(
     *         type="string",
     *         example="message"
     *     )
     * )
     */

    public function testerRegistration(Request $request,ParamsHelper $paramsHelper, LoggerInterface $testerLogger, TestersService $testersService,ResponseService $responseService,ValidatorInterface $validator)
    {
        $inputs = array_merge($request->files->all(),$request->request->all());
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($testerLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new RegisterTesterFormValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $testersService->SignUpTester();
    }
}
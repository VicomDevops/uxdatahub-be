<?php

namespace App\Controller;

use App\Service\ResponseService;
use App\Utils\ParamsHelper;
use Psr\Log\LoggerInterface;
use App\Service\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Validator\Password\ResetPasswordValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/")
 * @OA\Tag(name="ResetPassword")
 */
class ResetPasswordController extends AbstractController
{
    /**
     * @Route("offline/reset/password", name="api_offline_reset_password", methods={"POST"})
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="repassword", type="string"),
     *         @OA\Property(property="token", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Success response 200",
     *     @OA\JsonContent(
     *         type="object"
     *     )
     * )
     */
    public function offlineResetPassword(Request $request,ParamsHelper $paramsHelper, LoggerInterface $resetPasswordsLogger, ValidatorInterface $validator, PasswordService $passwordService, ResponseService $responseService)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($resetPasswordsLogger);
        $paramsHelper->flushInputWithLogger();
        if (count($validator->validate(new ResetPasswordValidator($inputs)))) {
            return $responseService->getResponseToClient(null, 400,  'general.params');
        }
        return $passwordService->resetPasswordOffline();
    }

    /**
     * @Route("offline/reset/password/temp", name="api_temp_offline_reset_password", methods={"POST"})
     */
    public function tempResetPassword(Request $request,ParamsHelper $paramsHelper, LoggerInterface $resetPasswordsLogger, ValidatorInterface $validator, PasswordService $passwordService, ResponseService $responseService)
    {
        $inputs = $request->request->all();
        $paramsHelper->setInputs($inputs);
        $paramsHelper->setLogger($resetPasswordsLogger);
        $paramsHelper->flushInputWithLogger();

        return $passwordService->tempresetPassword();
    }
}

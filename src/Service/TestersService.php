<?php

namespace App\Service;

use App\Entity\Tester;
use App\Repository\ClientTesterRepository;
use App\Repository\TesterRepository;
use App\Utils\ParamsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TestersService
{

    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private ResponseService $responseService;
    private ParamsHelper $paramsHelper;
    private PasswordGenerator $passwordGenerator;
    private Mailer $mailer;
    private UploadService $uploadService;

    const DEPARTEMENT_POSTAL_CODES = [
        'Hauts-de-France' => ['02', '59', '60', '62', '80'],
        'Normandie' => ['14', '27', '50', '61', '76'],
        'Bretagne' => ['22', '29', '35', '56'],
        'Centre-Val de Loire' => ['18', '28', '36', '37', '41', '45'],
        'Île-de-France' => ['75', '77', '78', '91', '92', '93', '94', '95'],
        'Grand Est' => ['08', '10', '51', '52', '54', '55', '57', '67', '68', '88'],
        'Pays de la Loire' => ['44', '49', '53', '72', '85'],
        'Bourgogne-Franche-Comté' => ['21', '25', '39', '58', '70', '71', '89', '90'],
        'Nouvelle-Aquitaine' => ['16', '17', '19', '23', '24', '33', '40', '47', '79', '86', '87'],
        'Auvergne-Rhône-Alpes' => ['03', '07', '15', '26', '38', '42', '43', '63', '69', '73', '74'],
        'Occitanie' => ['09', '11', '12', '30', '31', '32', '34', '46', '48', '65', '66', '81', '82'],
        'Provence-Alpes-Côte d\'Azur' => ['04', '05', '06', '13', '83', '84'],
        'Corse' => ['2A', '2B']
    ];

    public function __construct(UploadService $uploadService,TesterRepository $testerRepository,ClientTesterRepository $clientTesterRepository,Mailer $mailer,PasswordGenerator $passwordGenerator,ParamsHelper $paramsHelper,ResponseService $responseService,SerializerInterface $serializer,EntityManagerInterface $entityManager)
    {
        $this->serializer  = $serializer;
        $this->entityManager = $entityManager;
        $this->responseService = $responseService;
        $this->paramsHelper = $paramsHelper;
        $this->passwordGenerator = $passwordGenerator;
        $this->mailer = $mailer;
        $this->testerRepository = $testerRepository;
        $this->clientTesterRepository = $clientTesterRepository;
        $this->uploadService = $uploadService;
    }

    public function valdiateTester()
    {
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tester = $this->testerRepository->findOneBy(['id' => $inputs["tester_id"]]);
                if (!$tester)
                {
                    return $this->responseService->getResponseToClient(null,200,"admin.tester_not_found");
                }
            $tester->setIsActive(true)
                ->setState('user_ok');
            $password = $this->passwordGenerator->newPassword($tester);
            $this->mailer->sendPassword($tester, $password);
            $this->entityManager->persist($tester);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient(null,200,"admin.tester_validation_success");
        } catch (\Exception $exception)
        {
            return $this->responseService->getResponseToClient(null,500,$exception->getMessage());
        }
    }

    public function SignUpTester(){
        try {
            $inputs = $this->paramsHelper->getInputs();
            $tester = $this->serializer->deserialize(json_encode($inputs, true), Tester::class, 'json',['groups' => 'first_signup']);
            $tester->setIsActive(false)
                ->setRoles(['ROLE_TESTER'])
                ->setState('to_contact');
            $this->entityManager->persist($tester);
            $identityCardFront = $this->uploadService->uploadProfileImage($inputs["identityCardFront"],$tester);
            $tester->setIdentityCardFront($identityCardFront);
            $identityCardBack = $this->uploadService->uploadProfileImage($inputs["identityCardBack"],$tester);
            $tester->setIdentityCardBack($identityCardBack);
            $this->entityManager->flush();

            return $this->responseService->getResponseToClient();

        }catch (\Exception $exception){
            return $this->responseService->getResponseToClient($exception->getMessage(),500,'general.500');
        }
    }

    public function statistics(array $ids,string $panelType)
    {
            if($panelType == 'insight data')
            {
                $testers = $this->testerRepository->findBy(["id" => $ids]);
            }else{
                $testers = $this->clientTesterRepository->findBy(["id" => $ids]);
            }
            $genders = array_map(function($tester){return $tester->getGender()??null;},array_filter($testers));
            $countries = array_map(function($tester){return $tester->getCountry()??null;},array_filter($testers));
            $map = array_map(function($tester) {
                $country = $tester->getCountry();
                return ($country === 'France' || $country === 'france') ?
                    $this->checkPostalCode(substr((string)$tester->getPostalCode()??'00', 0, 2))
                    : 'Autre';
            }, $testers);
            $os = array_map(function($tester){return $tester->getOs()??null;},array_filter($testers));
            $socialMedia = array_map(function($tester){return $tester->getSocialMedia()??null;},array_filter($testers));
            $csp = array_map(function($tester){return $tester->getCsp()??null;},array_filter($testers));
            $studyLevel = array_map(function($tester){return $tester->getStudyLevel()??null;},array_filter($testers));

            $maritalStatus = array_map(function($tester){return $tester->getMaritalStatus()??null;},array_filter($testers));
            $ageRange = array_map(function($tester){
                    $age = $tester->getDateOfBirth()?$tester->getDateOfBirth()->diff(date_create('now'))->format('%Y'):null;
                    return $this->dateBirthRange($age)??null;
               
            },array_filter($testers));

            $result= [
                'gender' => $genders,
                'csp' => $csp,
                'countries' => $countries,
                "map"  => $map,
                'os' => $os,
                'socialMedia' => $socialMedia,
                'studyLevel' => $studyLevel,
                'maritalStatus' => $maritalStatus,
                'ageRange' => $ageRange
            ];
            $result = array_map(function($pourcentage){
                return $this->pourcentage($pourcentage);
                },$result);
            foreach ($result as $key => $value) {
                $result[$key] = [];
                foreach ($value as $k => $v) {
                    $result[$key][] = ['label' => $k, 'value' => $v];
                }
            }

            return $result;

    }
    public function pourcentage(array $arrayToCount){
        $filteredArray = array_filter($arrayToCount, function ($value) {
            return is_string($value) || is_int($value);
        });

        $arrayCountValues = array_count_values($filteredArray);
        return array_map(function($arrayValue)use ($arrayCountValues){
            return (int)round($arrayValue/array_sum($arrayCountValues) * 100);
            },$arrayCountValues);
    }

    public function dateBirthRange($age){
        if($age>10 and $age <20){
            return '10-20 age';
        }elseif($age <30){
            return '20-30 age';
        }elseif($age < 40){
            return '30-40 age';
        }elseif($age< 50){
            return '40-50 age';
        }elseif($age < 60){
            return '50-60 age';
        }elseif($age <70){
            return '60-70 age';
        }elseif($age < 80){
            return '70-80 age';
        }else return '>80 age';
    }

    public function checkPostalCode($code) {
        foreach (self::DEPARTEMENT_POSTAL_CODES as $departement => $codes) {
            if (in_array($code, $codes)) {
                return $departement;
            }
        }
        return '00';
    }
}
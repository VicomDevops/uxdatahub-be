<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\ClientTester;
use App\Entity\Scenario;
use App\Entity\Test;
use App\Entity\Tester;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
    const EMAIL = 'report@insightdata.fr';

    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendWelcomeMessage($user)
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($user->getEmail())
            ->subject('Merci de faire confiance à Insight Data')
            ->htmlTemplate('email/welcome.html.twig')
            ->context([
                'name' => $user->getName()
            ]);

        $this->mailer->send($email);
    }

    public function sendPassword($user, $url)
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($user->getEmail())
            ->subject('Insight Data – Réinitialiser le mot de passe')
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'name' => $user->getName(),
                'url' => $url,
            ]);
        $this->mailer->send($email);
    }

    public function sendNotification(Tester $tester, $scenario)
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($tester->getEmail())
            ->subject('Insight Data – New Scenario!')
            ->htmlTemplate('email/scenario.html.twig')
            ->context([
                'name' => $tester->getName(),
                'id'=>$scenario->getId()
            ]);

        $this->mailer->send($email);
    }
    public function sendNotificationClient(ClientTester $user,array $scenarios,$password=null)
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($user->getEmail())
            ->subject('Insight Data – New Scenario!')
            ->htmlTemplate('email/scenarioClientTester.html.twig')
            ->context([
                'name' => $user->getName(),
                'scenarios'=>$scenarios,
                'username' => $user->getEmail(),
                'password' =>  $password
            ]);

        $this->mailer->send($email);
    }

    public function sendPlayNotificationClient(ClientTester $user,Scenario $scenario,$password=null)
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($user->getEmail())
            ->subject('Insight Data – New Scenario!')
            ->htmlTemplate('email/playScenarioClientTester.html.twig')
            ->context([
                'name' => $user->getName(),
                'scenario'=>$scenario->getTitle(),
                'username' => $user->getEmail(),
                'password' =>  $password
            ]);

        $this->mailer->send($email);
    }


    public function validateClient(Client $client,$password){

        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($client->getEmail())
            ->subject('Bienvenue chez Insight Data')
            ->htmlTemplate('email/client.html.twig')
            ->context([
                'name'=>$client->getName(),
                'user' => $client->getEmail(),
                'password' =>  $password
            ]);

        $this->mailer->send($email);
    }

    public function sendAdminEmailNotification(Client $client){
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to(self::EMAIL)
            ->subject('Contrat DOCUSIGN')
            ->htmlTemplate('email/contrat_docusign.html.twig')
            ->context([
                'name'=>$client->getName(),
                'user' => $client->getEmail()
            ]);
        $this->mailer->send($email);
    }

    public function sendClientEmailNotification(Client $client){

        $email = (new TemplatedEmail())
        ->from(self::EMAIL)
        ->to($client->getEmail())
        ->subject('Votre Contrat avec Insight Data')
        ->htmlTemplate('email/contrat_client_docusign.html.twig')
        ->context([
            'name'=>$client->getName()
        ]);
        $this->mailer->send($email);

    }

    public function sendClientDemandeNotification(Client $client){
        $email = (new TemplatedEmail())
        ->from(self::EMAIL)
        ->to(self::EMAIL)
        ->subject('Demande Modification Contrat/SEPA Client')
        ->htmlTemplate('email/demande_client.html.twig')
        ->context([
            'name'=>$client->getName(),
            'user'=>$client->getEmail(),
            'mobile'=>$client->getPhone()
        ]);
        $this->mailer->send($email);
    }

    public function sendForgottenPasswordMail($user){
        $email = (new TemplatedEmail())
        ->from(self::EMAIL)
        ->to($user->getEmail())
        ->subject('Mot de passe oublié Insight Data')
        ->htmlTemplate('email/forgotten_password.html.twig')
        ->context([
            'name'=>$user->getName()
        ]);
        $this->mailer->send($email);
    }

    public function sendScheduledNotificationClient(ClientTester $user,Scenario $scenario): true|string
    {
        try {
            $email = (new TemplatedEmail())
                ->from(self::EMAIL)
                ->to($user->getEmail())
                ->subject('Insight Data – Rappel Scenario !')
                ->htmlTemplate('email/scenarioClientTester.html.twig')
                ->context([
                    'name' => $user->getName(),
                    'scenario' => $scenario->getTitle()
                ]);
            $this->mailer->send($email);
            return true;

        } catch (TransportExceptionInterface $e) {
                return $e->getMessage();
        }
    }

    public function resetScenarioNotification(ClientTester $user,Scenario $scenario): true|string
    {
        try {
            $email = (new TemplatedEmail())
                ->from(self::EMAIL)
                ->to($user->getEmail())
                ->subject('Insight Data – Reset Scenario !')
                ->htmlTemplate('email/resetScenarioClientTester.html.twig')
                ->context([
                    'name' => $user->getName(),
                    'scenario' => $scenario->getTitle()
                ]);
            $this->mailer->send($email);
            return true;

        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }

    public function confirmClientAccount(Client $client,$url){

        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($client->getEmail())
            ->subject('Bienvenue chez Insight Data')
            ->htmlTemplate('email/confirmClientAccount.html.twig')
            ->context([
                'name'=>$client->getName(),
                'user' => $client->getEmail(),
                'url' =>  $url
            ]);

        $this->mailer->send($email);
    }
    public function sendMailToTestersForInterruptedTests(Test $test){

        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($test->getClientTester()->getEmail())
            ->subject('Bienvenue chez Insight Data')
            ->htmlTemplate('email/clientTesterReminder.html.twig')
            ->context([
                'scenario' => $test->getScenario()->getTitle(),
            ]);

        $this->mailer->send($email);
    }

    public function resendPlayNotificationClient(ClientTester $user,array $scenarios,string $password)
    {
        $email = (new TemplatedEmail())
            ->from(self::EMAIL)
            ->to($user->getEmail())
            ->subject('Insight Data – New Scenario!')
            ->htmlTemplate('email/resentPlayScenarioClientTester.html.twig')
            ->context([
                'name' => $user->getName(),
                'scenarios'=> $scenarios,
                'username' => $user->getEmail(),
                'password' =>  $password
            ]);

        $this->mailer->send($email);
    }
}

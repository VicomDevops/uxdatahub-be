<?php

namespace App\Controller;

use App\Entity\Client;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * @Route("/api")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer)
    {
        $user = new Client();
        $user->setEmail('zouaoui@2m-advisory.fr')
            ->setPassword($passwordEncoder->encodePassword(
                $user,
                "123456"
            ))
            ->setName("Amine")
            ->setLastname("Zouaoui")
            ->setCompany("2m-advisory")
            ->setNbEmployees("1-10")
            ->setPhone("25707027")
            ->setProfession("DEV")
            ->setSector("IT")
            ->setUseCase("Entreprise: Plusieurs projets à tester");

        $entityManager->persist($user);
        $entityManager->flush();
        $mailer->sendWelcomeMessage($user);
        return $this->json(['mesage' => 'registred']);
    }

    /**
     * @Route("/api/user", name="current_user", methods={"GET"})
     */
    public function getCurrentUser(SerializerInterface $serializer)
    {
        $json = $serializer->serialize($this->getUser(), 'json', ["groups" => "current_user"]);
        return new Response($json, 200);
    }


}

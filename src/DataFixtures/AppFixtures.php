<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Tester;
use App\DataFixtures\DataFixtures;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Google\Service\Analytics\Resource\Data;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;


class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $dataFixtures;
    private $serializer;
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer,DataFixtures $dataFixtures,UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->dataFixtures = $dataFixtures;
        $this->serializer = $serializer;
        $this->entityManager    = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        try
        {
            $this->truncateTable(User::class);
            var_dump("Data truncated !");

            foreach ($this->dataFixtures->getDefaultUsers() as $data)
            {
                switch ($data['roles'])
                {
                    case ["ROLE_ADMIN"]:
                        $user = $this->serializer->deserialize(json_encode($data, true),Admin::class,'json');
                        break;
                    case ["ROLE_TESTER"]:
                        $user = $this->serializer->deserialize(json_encode($data, true),Tester::class,'json');
                        break;
                    case ["ROLE_CLIENT"]:
                        $user = $this->serializer->deserialize(json_encode($data, true),Client::class,'json');
                        break;
                    default:
                        var_dump("Data error Role User !");

                }
                $user->setPassword($this->passwordHasher->hashPassword($user,$data["password"]));
                $manager->persist($user);
                $manager->flush();
            }

            var_dump("Data inserted !");
        }catch (\Exception $exception)
        {
            var_dump('Error ! data not truncated ! '. $exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function truncateTable($className) {
        $cmd = $this->entityManager->getClassMetadata($className);
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $connection->executeQuery('TRUNCATE TABLE public.'.$cmd->getTableName(). ' CASCADE');
            $connection->commit();
            $this->entityManager->flush();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $connection->rollBack();
        }
    }
}

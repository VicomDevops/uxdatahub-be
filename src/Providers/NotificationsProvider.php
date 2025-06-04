<?php

namespace App\Providers;

use App\Entity\ClientTester;
use App\Entity\Notifications;
use App\Entity\Scenario;
use App\Entity\Test;
use Doctrine\ORM\EntityManagerInterface;

class NotificationsProvider
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function createNotification(ClientTester $clientTester,Scenario $scenario, Test|null $test)
    {
        $notifications = $this->entityManager->getRepository(Notifications::class)->findOneBy(["clientTester" => $clientTester,"panel" => $scenario->getPanel(),"scenarios" => $scenario, "test" => $test]);
        if (!$notifications)
        {
            $notifications = new Notifications();
            $notifications->setClientTester($clientTester);
            $notifications->setScenarios($scenario);
            $notifications->setTest($test);
            $notifications->setPanel($scenario->getPanel());
            $notifications->setNotificationNumber(0);
            $notifications->setLastNotifcationDate(new \DateTimeImmutable("now"));
        }else
        {
            $notifications->setLastNotifcationDate(new \DateTimeImmutable("now"));
            $notifications->setNotificationNumber(0);
        }
        $this->entityManager->persist($notifications);
        $this->entityManager->flush();
    }

    public function updateNotification(ClientTester  $clientTester,ClientTester $NewclientTester,$panel,$scenarios): bool
    {
        $notifications = $this->entityManager->getRepository(Notifications::class)->findBy(["clientTester" => $clientTester,"panel" => $panel,"scenarios" => $scenarios]);
        if ($notifications)
        {
            foreach ($notifications as $notification)
            {
                $notification->setClientTester($NewclientTester);
                $notification->setLastNotifcationDate(new \DateTimeImmutable("now"));
                $notification->setNotificationNumber(0);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            }
            return true;
        }
        return false;
    }

}
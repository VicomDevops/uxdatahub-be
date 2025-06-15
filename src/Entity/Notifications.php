<?php

namespace App\Entity;

use App\Repository\NotificationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationsRepository::class)
 */
class Notifications
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications" , cascade={"persist"})
     * @ORM\JoinColumn(name="clientTester", referencedColumnName="id")
     */
    private ?ClientTester $clientTester = null;

    /**
     * @ORM\ManyToOne(inversedBy="scenarios", cascade={"persist"})
     * @ORM\JoinColumn(name="scenarios", referencedColumnName="id")
     */
    private ?Scenario $scenarios = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinColumn(name="panel", referencedColumnName="id")
     */
    private ?Panel $panel = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinColumn(name="test", referencedColumnName="id")
     */
    private ?Test $test = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $notificationNumber = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?\DateTimeImmutable $lastNotificationDate = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?\DateTimeImmutable $created_at;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?\DateTimeImmutable $updated_at = null;


    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientTester(): ?ClientTester
    {
        return $this->clientTester;
    }

    public function setClientTester(?ClientTester $clientTester): static
    {
        $this->clientTester = $clientTester;

        return $this;
    }

    public function getScenarios(): ?Scenario
    {
        return $this->scenarios;
    }

    public function setScenarios(?Scenario $scenarios): static
    {
        $this->scenarios = $scenarios;

        return $this;
    }

    public function getPanel(): ?Panel
    {
        return $this->panel;
    }

    public function setPanel(?Panel $panel): static
    {
        $this->panel = $panel;

        return $this;
    }


    public function getTest(): ?Test
    {
        return $this->test;
    }

    public function setTest(?Test $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getNotificationNumber(): ?string
    {
        return $this->notificationNumber;
    }

    public function setNotificationNumber(?string $notificationNumber): static
    {
        $this->notificationNumber = $notificationNumber;

        return $this;
    }

    public function getLastNotifcationDate(): ?\DateTimeImmutable
    {
        return $this->lastNotificationDate;
    }

    public function setLastNotifcationDate(\DateTimeImmutable $lastNotificationDate): static
    {
        $this->lastNotificationDate = $lastNotificationDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

}

<?php

namespace App\Entity;

use App\Repository\LicenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LicenceRepository::class)
 */
class Licence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @ORM\OneToOne(targetEntity=Client::class, inversedBy="licence", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

        /**
     * @ORM\ManyToOne(targetEntity=LicenceCategory::class, inversedBy="licences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $licenceCategory;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getLicenceCategory(): ?LicenceCategory
    {
        return $this->licenceCategory;
    }

    public function setLicenceCategory(?LicenceCategory $licenceCategory): self
    {
        $this->licenceCategory = $licenceCategory;

        return $this;
    }
}

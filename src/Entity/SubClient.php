<?php

namespace App\Entity;

use App\Repository\SubClientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubClientRepository::class)
 */
class SubClient extends User
{
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $lastname;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="subClients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="boolean")
     */
    private $writeRights = false;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getWriteRights(): ?bool
    {
        return $this->writeRights;
    }

    public function setWriteRights(bool $writeRights): self
    {
        $this->writeRights = $writeRights;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}

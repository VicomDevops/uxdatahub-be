<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 */
class Admin extends User
{
    /**
     * @Assert\NotBlank(message="Ce champ est obligatoire")
     * @Assert\Regex("/^[a-zA-Z\s]{1,50}$/", message="Ce champ ne doit pas contenir des chiffres ou des caractères spéciaux")
     * @ORM\Column(type="string", length=100)
     * @Groups({"admin", "current_user"})
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Ce champ est obligatoire")
     * @Assert\Regex("/^[a-zA-Z\s]{1,50}$/", message="Ce champ ne doit pas contenir des chiffres ou des caractères spéciaux")
     * @ORM\Column(type="string", length=100)
     * @Groups({"admin", "current_user"})
     */
    private $lastname;

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

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}

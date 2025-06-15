<?php

namespace App\Entity;

use App\Repository\HelpRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HelpRepository::class)
 */
class Help
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_help"})
     */
    private $Subject;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_help"})
     */
    private $Nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_help"})
     */
    private $Prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_help"})
     */
    private $Phone;

    /**
     * @ORM\Column(type="text")
     * @Groups({"create_help"})
     */
    private $Commentaire;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="helps")
     */
    private $Launcher;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->Subject;
    }

    public function setSubject(string $Subject): self
    {
        $this->Subject = $Subject;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): self
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->Phone;
    }

    public function setPhone(string $Phone): self
    {
        $this->Phone = $Phone;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->Commentaire;
    }

    public function setCommentaire(string $Commentaire): self
    {
        $this->Commentaire = $Commentaire;

        return $this;
    }

    public function getLauncher(): ?User
    {
        return $this->Launcher;
    }

    public function setLauncher(?User $Launcher): self
    {
        $this->Launcher = $Launcher;

        return $this;
    }


}

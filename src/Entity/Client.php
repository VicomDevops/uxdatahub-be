<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client extends User
{
    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @Assert\Regex("/^[a-zA-Z\s]{1,50}$/", message="Ce champ ne doit pas contenir des chiffres ou des caractères spéciaux")
     * @ORM\Column(type="string", length=100)
     * @Groups({"new_client", "current_user", "signup","update_client"})
     */
    private $name;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @Assert\Regex("/^[a-zA-Z\s]{1,50}$/", message="Ce champ ne doit pas contenir des chiffres ou des caractères spéciaux")
     * @ORM\Column(type="string", length=100)
     * @Groups({"new_client", "current_user", "signup","update_client"})
     */
    private $lastname;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=30)
     * @Groups({"new_client", "signup", "current_user","update_client"})
     */
    private $phone;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=255)
     * @Groups({"new_client", "current_user", "signup","update_client"})
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"signup", "current_user","update_client"})
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"signup", "current_user","update_client"})
     */
    private $sector;

    /**
     * @ORM\Column(type="string", length=15)
     * @Groups({"signup", "current_user","update_client"})
     */
    private $nbEmployees;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"new_client", "signup", "current_user"})
     */
    private $useCase;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="client")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=SubClient::class, mappedBy="client")
     */
    private $subClients;


    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $stripeId;

    /**
     * @ORM\OneToMany(targetEntity=Scenario::class, mappedBy="client")
     * @MaxDepth(1)
     */
    private $scenarios;

    /**
     * @ORM\OneToMany(targetEntity=Contract::class, mappedBy="client", cascade={"persist", "remove"})
     */
    private $contracts;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contractLink;

     /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"signup","update_client"})
     */
    private $privacyPolicy;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     * @Groups({"signup","update_client"})
     */
    private $cgu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"update_client"})
     */
    private $profileImage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"signup", "current_user","update_client"})
     */
    private $city;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->subClients = new ArrayCollection();
        $this->scenarios = new ArrayCollection();
        $this->contracts= new ArrayCollection();
    }

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(string $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getNbEmployees(): ?string
    {
        return $this->nbEmployees;
    }

    public function setNbEmployees(string $nbEmployees): self
    {
        $this->nbEmployees = $nbEmployees;

        return $this;
    }

    public function getUseCase(): ?string
    {
        return $this->useCase;
    }

    public function setUseCase(string $useCase): self
    {
        $this->useCase = $useCase;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setClient($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getClient() === $this) {
                $comment->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SubClient[]
     */
    public function getSubClients(): Collection
    {
        return $this->subClients;
    }

    public function addSubClient(SubClient $subClient): self
    {
        if (!$this->subClients->contains($subClient)) {
            $this->subClients[] = $subClient;
            $subClient->setClient($this);
        }

        return $this;
    }

    public function removeSubClient(SubClient $subClient): self
    {
        if ($this->subClients->contains($subClient)) {
            $this->subClients->removeElement($subClient);
            // set the owning side to null (unless already changed)
            if ($subClient->getClient() === $this) {
                $subClient->setClient(null);
            }
        }

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(?string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * @return Collection|Scenario[]
     */
    public function getScenarios(): Collection
    {
        return $this->scenarios;
    }

    public function addScenario(Scenario $scenario): self
    {
        if (!$this->scenarios->contains($scenario)) {
            $this->scenarios[] = $scenario;
            $scenario->setClient($this);
        }

        return $this;
    }

    public function removeScenario(Scenario $scenario): self
    {
        if ($this->scenarios->contains($scenario)) {
            $this->scenarios->removeElement($scenario);
            // set the owning side to null (unless already changed)
            if ($scenario->getClient() === $this) {
                $scenario->setClient(null);
            }
        }

        return $this;
    }
    /**
     * Get the value of contractLink
     */ 
    public function getContractLink()
    {
        return $this->contractLink;
    }

    /**
     * Set the value of contractLink
     *
     * @return  self
     */ 
    public function setContractLink($contractLink)
    {
        $this->contractLink = $contractLink;

        return $this;
    }

    /**
     * @return Collection|Contract[]
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts[] = $contract;
            $contract->setClient($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->contains($contract)) {
            $this->contracts->removeElement($contract);
        }

        return $this;
    }

    /**
     * Get the value of privacyPolicy
     */ 
    public function getPrivacyPolicy()
    {
        return $this->privacyPolicy;
    }

    /**
     * Set the value of privacyPolicy
     *
     * @return  self
     */ 
    public function setPrivacyPolicy($privacyPolicy)
    {
        $this->privacyPolicy = $privacyPolicy;

        return $this;
    }

    /**
     * Get the value of cgu
     */ 
    public function getCgu()
    {
        return $this->cgu;
    }

    /**
     * Set the value of cgu
     *
     * @return  self
     */ 
    public function setCgu($cgu)
    {
        $this->cgu = $cgu;

        return $this;
    }

    /**
     * Get the value of profileImage
     */ 
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * Set the value of profileImage
     *
     * @return  self
     */ 
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}

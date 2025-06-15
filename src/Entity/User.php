<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"client"="Client", "clienttester"="ClientTester","admin"="Admin", "tester"="Tester", "subclient"="SubClient"})
 * @ORM\Table(name="`user`")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"new_client", "admin", "current_user", "google_step", "tester_video","tester_id","data_by_scenario","scenario_details","get_panel","panel_details","face_reco_scenario"})
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Ce champ est obligatoire")
     * @Assert\Email(message="Email non valide")
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"new_client", "admin", "current_user","get_panel","panel_details"})
     * @Groups({"signup","create_panel", "get_all","first_signup"})
     */
    protected $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"admin", "current_user"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"current_user"})
     */
    private $isFirstConnection = true;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"new_client", "admin","current_user"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"new_client", "admin"})
     */
    private $state = "to_contact";

    /**
     * @Groups({"admin"})
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $username;

    /**
     * @ORM\OneToMany(targetEntity=Help::class, mappedBy="Launcher")
     */
    private $helps;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"current_user"})
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\OneToMany(targetEntity=Session::class, mappedBy="user")
     */
    private $user;

    public function __construct()
    {
        $this->helps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsFirstConnection(): ?bool
    {
        return $this->isFirstConnection;
    }

    public function setIsFirstConnection(bool $isFirstConnection): self
    {
        $this->isFirstConnection = $isFirstConnection;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|Help[]
     */
    public function getHelps(): Collection
    {
        return $this->helps;
    }

    public function addHelp(Help $help): self
    {
        if (!$this->helps->contains($help)) {
            $this->helps[] = $help;
            $help->setLauncher($this);
        }

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified??false;
    }

    public function setConfirmationToken(string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified??false;

        return $this;
    }

    public function removeHelp(Help $help): self
    {
        if ($this->helps->removeElement($help)) {
            // set the owning side to null (unless already changed)
            if ($help->getLauncher() === $this) {
                $help->setLauncher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(Session $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->setUser($this);
        }

        return $this;
    }

    public function removeUser(Session $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getUser() === $this) {
                $user->setUser(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}

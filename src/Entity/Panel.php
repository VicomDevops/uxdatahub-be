<?php

namespace App\Entity;

use App\Repository\PanelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PanelRepository::class)
 */
class Panel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_panel","panel_details","view_scenario"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_panel", "get_panel", "view_scenario","panel_details"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_panel", "get_panel", "view_scenario"})
     */
    private $scenarioName;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"create_panel", "get_panel", "view_scenario","scenario_details","panel_details"})
     */
    private $testersNb;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $csp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $os;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $studyLevel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $maritalStatus;

    /**
     * @ORM\OneToMany(targetEntity=Scenario::class, mappedBy="panel")
     * @Groups({"panel_details"})
     */
    private $scenarios;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $minAge;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"create_panel", "get_panel"})
     */
    private $maxAge;

    /**
     * @ORM\ManyToMany(targetEntity=ClientTester::class, mappedBy="panels", cascade={"persist"})
     * @Groups({"get_panel","panel_details"})
     */
    private $clientTesters;

    /**
     * @ORM\ManyToMany(targetEntity=Tester::class, mappedBy="panelsInsight", cascade={"persist"})
     * @Groups({"create_panel","get_panel"})
     */
    private $insightTesters;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"create_panel", "get_panel","scenario_details","panel_details"})
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Notifications::class, mappedBy="panel", cascade={"persist"})
     */
    private Collection $notifications;

    public function __construct()
    {
        $this->scenarios = new ArrayCollection();
        $this->clientTesters = new ArrayCollection();
        $this->insightTesters = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getScenarioName(): ?string
    {
        return $this->scenarioName;
    }

    public function setScenarioName(string $scenarioName): self
    {
        $this->scenarioName = $scenarioName;

        return $this;
    }

    public function getTestersNb(): ?int
    {
        return $this->testersNb;
    }

    public function setTestersNb(int $testersNb): self
    {
        $this->testersNb = $testersNb;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCsp(): ?string
    {
        return $this->csp;
    }

    public function setCsp(?string $csp): self
    {
        $this->csp = $csp;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getStudyLevel(): ?string
    {
        return $this->studyLevel;
    }

    public function setStudyLevel(?string $studyLevel): self
    {
        $this->studyLevel = $studyLevel;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?string $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

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
            $scenario->setPanel($this);
        }

        return $this;
    }

    public function removeScenario(Scenario $scenario): self
    {
        if ($this->scenarios->contains($scenario)) {
            $this->scenarios->removeElement($scenario);
            // set the owning side to null (unless already changed)
            if ($scenario->getPanel() === $this) {
                $scenario->setPanel(null);
            }
        }

        return $this;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): self
    {
        $this->minAge = $minAge;

        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): self
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * @return Collection|ClientTester[]
     */
    public function getClientTesters(): Collection
    {
        return $this->clientTesters;
    }


    /**
     * @return Collection|InsightTester[]
     */
    public function getInsightTesters(): Collection
    {
        return $this->insightTesters;
    }

    public function addClientTester(ClientTester $clientTester): self
    {
        if (!$this->clientTesters->contains($clientTester)) {
            $this->clientTesters[] = $clientTester;
            $clientTester->addPanel($this);
        }

        return $this;
    }

    public function removeClientTester(ClientTester $clientTester): self
    {
        if ($this->clientTesters->contains($clientTester)) {
            $this->clientTesters->removeElement($clientTester);
            $clientTester->removePanel($this);
        }

        return $this;
    }

    public function addInsightTester(Tester $tester): self
    {
        if (!$this->insightTesters->contains($tester)) {
            $this->insightTesters[] = $tester;
            $tester->addPanel($this);
        }

        return $this;
    }


    public function removeInsightTester(Tester $tester): self
    {
        if ($this->insightTesters->contains($tester)) {
            $this->insightTesters->removeElement($tester);
            $tester->removePanel($this);
        }

        return $this;
    }
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Notifications>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notifications $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setPanel($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPanel() === $this) {
                $notification->setPanel(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ScenarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ScenarioRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Scenario
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"alldata_select","view_scenarios", "view_scenario", "analyze_by_step", "face_reco_scenario","scenario_details","panel_details"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"alldata_select","view_scenarios", "create_scenario", "get_test", "view_scenario", "analyze_by_step", "face_reco_scenario","scenario_details","panel_details","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups({"view_scenarios", "create_scenario", "get_test", "view_scenario"})
     */
    private $product;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"view_scenarios", "create_scenario", "view_scenario"})
     */
    private $isUnique;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"view_scenarios", "create_scenario", "get_test", "view_scenario"})
     */
    private $isModerate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"view_scenarios", "view_scenario"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="scenarios")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"scenario_details"})
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity=Step::class, mappedBy="scenario", cascade={"persist","remove"})
     * @Groups({"view_scenario", "analyze_by_step","scenario_details","face_reco_scenario"})
     */
    private $steps;

    /**
     * @ORM\ManyToOne(targetEntity=Panel::class, inversedBy="scenarios")
     * @Groups({"view_scenario","scenario_details"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $panel;

    /**
     * @ORM\OneToMany(targetEntity=Test::class, mappedBy="scenario",cascade={"persist", "remove"})
     * @Groups({"scenario_details"})
     */
    private $tests;

    /**
     * @Groups({"view_scenarios"})
     */
    private $moderate;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     * @Groups({"view_scenarios", "create_scenario", "get_test", "view_scenario"})
     */
    private $langue;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"view_scenarios", "create_scenario", "get_test", "view_scenario"})
     */
    private $validate;

    /**
     * @ORM\Column(type="integer",options={"default"= 0},nullable=true)
     * @Groups({"view_scenarios", "view_scenario", "analyze_by_step", "face_reco_scenario"})
     */
    private $etat;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @Groups({"view_scenarios", "view_scenario"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @Groups({"view_scenarios", "view_scenario"})
     */
    private $closedAt;

    /**
     * @ORM\Column(type="integer",nullable=true,options={"default"= 0})
     * @Groups({"view_scenarios", "create_scenario", "view_scenario"})
     */
    private $isTested= 0;

    /**
     * @ORM\Column(type="integer",nullable=true)
     * @Groups({"alldata_select","view_scenarios", "create_scenario", "view_scenario"})
     */
    private $progress;

    /**
     * @ORM\OneToMany(targetEntity=Notifications::class, mappedBy="scenarios", cascade={"persist"})
     */
    private Collection $scenarios;

    public function getModerate(): string
    {
        return $this->isModerate ? 'Modéré' : 'Non Modéré';
    }

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->etat = 0;
        $this->scenarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getIsUnique(): ?bool
    {
        return $this->isUnique;
    }

    public function setIsUnique(bool $isUnique): self
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    public function getIsModerate(): ?bool
    {
        return $this->isModerate;
    }

    public function setIsModerate(bool $isModerate): self
    {
        $this->isModerate = $isModerate;

        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->format('d/m/Y');
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

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

    /**
     * @return Collection|Step[]
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps[] = $step;
            $step->setScenario($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
            // set the owning side to null (unless already changed)
            if ($step->getScenario() === $this) {
                $step->setScenario(null);
            }
        }

        return $this;
    }

    public function getPanel(): ?Panel
    {
        return $this->panel;
    }

    public function setPanel(?Panel $panel): self
    {
        $this->panel = $panel;

        return $this;
    }

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->tests->contains($test)) {
            $this->tests[] = $test;
            $test->setScenario($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getScenario() === $this) {
                $test->setScenario(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLangue()
    {
        return $this->langue;
    }

    /**
     * @param mixed $langue
     */
    public function setLangue($langue): void
    {
        $this->langue = $langue;
    }

    /**
     * @return mixed
     */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * @param mixed $validate
     */
    public function setValidate($validate): void
    {
        $this->validate = $validate;
    }

    /**
     * @return mixed
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param mixed $etat
     */
    public function setEtat($etat): void
    {
        $this->etat = $etat;
    }

    /**
     * @return mixed
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @param mixed $startedAt
     */
    public function setStartedAt($startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @ORM\PrePersist()
     */

    public function setStartedAtVoid(): void
    {
        $this->startedAt = null;
    }

    /**
     * @return mixed
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @param mixed $closedAt
     */
    public function setClosedAt($closedAt): void
    {
        $this->closedAt = $closedAt;
    }

    function deep_clone(Step $step): Step
    {
        $newStep= new Step();
        return unserialize(serialize($step));
    }
    public function __clone()
	{
        $this->etat=1;
	}

    public function getIsTested(): bool
    {
        return $this->isTested;
    }

    public function setIsTested(bool $isTested): void
    {
        $this->isTested = $isTested;
    }

    /**
     * Get the value of progress
     */ 
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set the value of progress
     *
     * @return  self
     */ 
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * @return Collection<int, Notifications>
     */
    public function getScenarios(): Collection
    {
        return $this->scenarios;
    }

    public function addScenario(Notifications $scenario): static
    {
        if (!$this->scenarios->contains($scenario)) {
            $this->scenarios->add($scenario);
            $scenario->setScenarios($this);
        }

        return $this;
    }

    public function removeScenario(Notifications $scenario): static
    {
        if ($this->scenarios->removeElement($scenario)) {
            // set the owning side to null (unless already changed)
            if ($scenario->getScenarios() === $this) {
                $scenario->setScenarios(null);
            }
        }

        return $this;
    }
}

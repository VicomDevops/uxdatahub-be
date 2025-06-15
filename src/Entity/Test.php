<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TestRepository::class)
 */
class Test
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_test", "google_step", "google_tester","face_reco_scenario"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"get_test"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"google_step","tester_video"})
     */
    private $video;

    /**
     * @ORM\ManyToOne(targetEntity=Scenario::class, inversedBy="tests",cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_test","targeted_recommendations","targeted_recommendations"})
     */
    private $scenario;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="test", cascade={"persist"})
     * @Groups({"get_test","google_tester","face_reco_scenario","targeted_recommendations"})
     */
    private $answers;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"get_test"})
     */
    private $state;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"get_test"})
     */
    private $finishedAt;

    /**
     * @ORM\ManyToOne(targetEntity=ClientTester::class, inversedBy="tests")
     * @Groups({"google_step","tester_video","scenario_details","face_reco_scenario"})
     */
    private $clientTester;

    /**
     * @ORM\ManyToOne(targetEntity=Tester::class, inversedBy="tests")
     * @Groups({"google_step","tester_video"})
     */
    private $tester;

    /**
     * @ORM\Column(type="integer",options={"default"= 0},nullable=true)
     * @Groups({"view_scenario","scenario_details"})
     */
    private $etat;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $average;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"get_test", "google_tester","google_step","scenario_details","data_by_scenario"})
     */
    private $isAnalyzed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"get_test", "google_tester","google_step","view_scenario","data_by_scenario"})
     */
    private $isInterrupted;

    /**
     * @ORM\OneToMany(targetEntity=Notifications::class, mappedBy="test", cascade={"remove"})
     */
    private Collection $notifications;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageComments;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getScenario(): ?Scenario
    {
        return $this->scenario;
    }

    public function setScenario(?Scenario $scenario): self
    {
        $this->scenario = $scenario;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setTest($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getTest() === $this) {
                $answer->setTest(null);
            }
        }

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

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getClientTester(): ?ClientTester
    {
        return $this->clientTester;
    }

    public function setClientTester(?ClientTester $clientTester): self
    {
        $this->clientTester = $clientTester;

        return $this;
    }

    public function getTester(): ?Tester
    {
        return $this->tester;
    }

    public function setTester(?Tester $tester): self
    {
        $this->tester = $tester;

        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(?float $average): self
    {
        $this->average = $average;

        return $this;
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
    public function getIsAnalyzed()
    {
        return $this->isAnalyzed;
    }

    /**
     * @param mixed $isAnalyzed
     */
    public function setIsAnalyzed($isAnalyzed): void
    {
        $this->isAnalyzed = $isAnalyzed;
    }

    /**
     * @return mixed
     */
    public function getIsInterrupted()
    {
        return $this->isInterrupted;
    }

    /**
     * @param mixed $isInterrupted
     */
    public function setIsInterrupted($isInterrupted): void
    {
        $this->isInterrupted = $isInterrupted;
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
            $notification->setTest($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getTest() === $this) {
                $notification->setTest(null);
            }
        }

        return $this;
    }

    public function getAverageComments(): ?float
    {
        return $this->averageComments;
    }

    public function setAverageComments(?float $averageComments): self
    {
        $this->averageComments = $averageComments;

        return $this;
    }

    /**
     * Get a string representation of the Test entity.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }

}

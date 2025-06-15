<?php

namespace App\Entity;

use App\Repository\StepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=StepRepository::class)
 */
class Step
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"view_scenario", "analyze_by_step", "get_test", "get_step", "face_reco_scenario", "google_step", "google_tester","face_reco_scenario","answer_face_shots_emotion_per_photo"})
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"create_step", "view_scenario", "get_step","targeted_recommendations"})
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"create_step", "view_scenario", "get_step"})
     */
    private $instruction;

    /**
     * @ORM\Column(type="text")
     * @Groups({"create_step", "view_scenario", "get_test", "get_step","google_step","video_answer_tester","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity=Scenario::class, inversedBy="steps")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $scenario;

    /**
     * @ORM\ManyToOne(targetEntity=QuestionChoices::class, inversedBy="steps", cascade={"persist"})
     * @Groups({"create_step", "view_scenario", "get_test","targeted_recommendations"})
     */
    private $questionChoices;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"create_step", "view_scenario", "get_step", "face_reco_scenario", "google_tester","video_answer_tester","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $number;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="step", cascade={"persist", "remove"})
     * @Groups({"create_step", "view_scenario", "face_reco_scenario", "get_step", "google_step","scenario_details"})
     */
    private $answers;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step", "get_step","targeted_recommendations"})
     */
    private $average;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step", "get_step","targeted_recommendations"})
     */
    private $deviation;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $joy;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $sorrow;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $anger;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $surprise;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $confidence;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step", "get_step"})
     */
    private $averageComments;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step", "get_step"})
     */
    private $deviationComments;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $emotionsAVG = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $emotionsDeviation = [];

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): self
    {
        $this->instruction = $instruction;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

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

    public function getQuestionChoices(): ?QuestionChoices
    {
        return $this->questionChoices;
    }

    public function setQuestionChoices(?QuestionChoices $questionChoices): self
    {
        $this->questionChoices = $questionChoices;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

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
            $answer->setStep($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getStep() === $this) {
                $answer->setStep(null);
            }
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

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(?float $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getDeviation(): ?float
    {
        return $this->deviation;
    }

    public function setDeviation(?float $deviation): self
    {
        $this->deviation = $deviation;

        return $this;
    }

    public function getJoy(): ?float
    {
        return $this->joy;
    }

    public function setJoy(?float $joy): self
    {
        $this->joy = $joy;

        return $this;
    }

    public function getSorrow(): ?float
    {
        return $this->sorrow;
    }

    public function setSorrow(?float $sorrow): self
    {
        $this->sorrow = $sorrow;

        return $this;
    }

    public function getAnger(): ?float
    {
        return $this->anger;
    }

    public function setAnger(?float $anger): self
    {
        $this->anger = $anger;

        return $this;
    }

    public function getSurprise(): ?float
    {
        return $this->surprise;
    }

    public function setSurprise(?float $surprise): self
    {
        $this->surprise = $surprise;

        return $this;
    }

    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(?float $confidence): self
    {
        $this->confidence = $confidence;

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

    public function getDeviationComments(): ?float
    {
        return $this->deviationComments;
    }

    public function setDeviationComments(?float $deviationComments): self
    {
        $this->deviationComments = $deviationComments;

        return $this;
    }

    public function __clone()
	{
        $this->average=null;
        $this->deviation=null;
        $this->averageComments=null;
        $this->deviationComments=null;
	}

    public function getEmotionsAVG(): array
    {
        return $this->emotionsAVG;
    }

    public function setEmotionsAVG(array $emotionsAVG): void
    {
        $this->emotionsAVG = $emotionsAVG;
    }

    public function getEmotionsDeviation(): array
    {
        return $this->emotionsDeviation;
    }

    public function setEmotionsDeviation(array $emotionsDeviation): void
    {
        $this->emotionsDeviation = $emotionsDeviation;
    }
}

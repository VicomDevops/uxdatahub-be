<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;
/**
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 */
class Answer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_test", "analyze_by_step_and_tester", "face_reco_scenario", "get_step", "google_step", "google_tester","data_by_scenario","video_answer_tester"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get_test", "analyze_by_step_and_tester", "get_step", "google_step", "google_tester","data_by_scenario","video_answer_tester","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $answer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get_test", "analyze_by_step_and_tester", "get_step", "google_step", "google_tester","data_by_scenario","video_answer_tester","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=Step::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_test", "google_tester","video_answer_tester","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $step;

    /**
     * @ORM\ManyToOne(targetEntity=Test::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"google_step","data_by_scenario"})
     */
    private $test;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"analyze_by_step_and_tester", "get_step", "google_step", "google_tester","video_answer_tester"})
     */
    private $videoText;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step_and_tester", "google_step", "google_tester","video_answer_tester","targeted_recommendations"})
     */
    private $magnitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step_and_tester", "google_step", "google_tester","video_answer_tester","scenario_details","targeted_recommendations"})
     */
    private $score;

    /**
     * @ORM\OneToMany(targetEntity=Sentence::class, mappedBy="answer" , cascade={"remove"})
     * @Groups({"analyze_by_step_and_tester", "google_step", "google_tester"})
     */
    private $sentences;

    /**
     * @ORM\OneToMany(targetEntity=Salience::class, mappedBy="answer", cascade={"remove"})
     * @Groups({"google_step", "google_tester"})
     */
    private $saliences;

    /**
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"get_step"})
     */
    private $startAt;

    /**
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"get_step"})
     */
    private $endAt;


    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"analyze_by_step_and_tester", "get_step", "google_tester","video_answer_tester","scenario_details","targeted_recommendations"})
     */

    private $duration;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="answer", cascade={"persist","remove"})
     * @Groups({"google_step","video_answer_tester"})
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=FaceShot::class, mappedBy="answer")
     * @Groups({"answer_face_shots","answer_face_shots_emotion_per_photo"})
     */
    private $faceShots;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $calm;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $angry;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $sad;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $confused;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $disgusted;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $surprised;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $happy;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $fear;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"face_reco_scenario"})
     */
    private $confidence;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"google_step","google_tester","video_answer_tester"})
     */
    private $video;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"google_step","video_answer_tester","face_reco_scenario","answer_face_shots_emotion_per_photo"})
     */
    private $clientComment;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step_and_tester", "google_step", "google_tester","video_answer_tester","face_reco_scenario"})
     */
    private $scoreVideo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"analyze_by_step_and_tester", "google_step", "google_tester","video_answer_tester"})
     */
    private $magnitudeVideo;

    /**
     * @ORM\ManyToOne(targetEntity="ClientTester", inversedBy="answers")
     * @ORM\JoinColumn(name="client_tester_id", referencedColumnName="id")
     * @Groups({"data_by_scenario","video_answer_tester","scenario_details","face_reco_scenario","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $clientTester;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $magnitudeComments;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $scoreComments;


    public function __construct()
    {
        $this->sentences = new ArrayCollection();
        $this->saliences = new ArrayCollection();
        $this->faceShots = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(?Step $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getTest(): ?Test
    {
        return $this->test;
    }

    public function setTest(?Test $test): self
    {
        $this->test = $test;

        return $this;
    }

    public function getVideoText(): ?string
    {
        return $this->videoText;
    }

    public function setVideoText(?string $videoText): self
    {
        $this->videoText = $videoText;

        return $this;
    }

    public function getMagnitude(): ?float
    {
        return $this->magnitude;
    }

    public function setMagnitude(?float $magnitude): self
    {
        $this->magnitude = $magnitude;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(?float $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return Collection|Sentence[]
     */
    public function getSentences(): Collection
    {
        return $this->sentences;
    }

    public function addSentence(Sentence $sentence): self
    {
        if (!$this->sentences->contains($sentence)) {
            $this->sentences[] = $sentence;
            $sentence->setAnswer($this);
        }

        return $this;
    }

    public function removeSentence(Sentence $sentence): self
    {
        if ($this->sentences->removeElement($sentence)) {
            // set the owning side to null (unless already changed)
            if ($sentence->getAnswer() === $this) {
                $sentence->setAnswer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Salience[]
     */
    public function getSaliences(): Collection
    {
        return $this->saliences;
    }

    public function addSalience(Salience $salience): self
    {
        if (!$this->saliences->contains($salience)) {
            $this->saliences[] = $salience;
            $salience->setAnswer($this);
        }

        return $this;
    }

    public function removeSalience(Salience $salience): self
    {
        if ($this->saliences->removeElement($salience)) {
            // set the owning side to null (unless already changed)
            if ($salience->getAnswer() === $this) {
                $salience->setAnswer(null);
            }
        }

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return Collection|FaceShot[]
     */
    public function getFaceShots(): Collection
    {
        return $this->faceShots;
    }

    public function addFaceShot(FaceShot $faceShot): self
    {
        if (!$this->faceShots->contains($faceShot)) {
            $this->faceShots[] = $faceShot;
            $faceShot->setAnswer($this);
        }

        return $this;
    }

    public function removeFaceShot(FaceShot $faceShot): self
    {
        if ($this->faceShots->removeElement($faceShot)) {
            // set the owning side to null (unless already changed)
            if ($faceShot->getAnswer() === $this) {
                $faceShot->setAnswer(null);
            }
        }

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
            $comment->setAnswer($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->steps->contains($comment)) {
            $this->steps->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAnswer() === $this) {
                $comment->setAnswer(null);
            }
        }

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

    public function getClientComment(): ?string
    {
        return $this->clientComment;
    }

    public function setClientComment(?string $clientComment): self
    {
        $this->clientComment = $clientComment;

        return $this;
    }

    public function getScoreVideo(): ?float
    {
        return $this->scoreVideo;
    }

    public function setScoreVideo(?float $scoreVideo): self
    {
        $this->scoreVideo = $scoreVideo;

        return $this;
    }

    public function getMagnitudeVideo(): ?float
    {
        return $this->magnitudeVideo;
    }

    public function setMagnitudeVideo(?float $magnitudeVideo): self
    {
        $this->magnitudeVideo = $magnitudeVideo;

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


    public function getDuration()
    {
        $durationAsString = strval($this->duration);
        return substr($durationAsString, 0, strpos($durationAsString, '.')+3);
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMagnitudeComments(): ?float
    {
        return $this->magnitudeComments;
    }

    public function setMagnitudeComments(?float $magnitudeComments): self
    {
        $this->magnitudeComments = $magnitudeComments;

        return $this;
    }

    public function getScoreComments(): ?float
    {
        return $this->scoreComments;
    }

    public function setScoreComments(?float $scoreComments): self
    {
        $this->scoreComments = $scoreComments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCalm()
    {
        return $this->calm;
    }

    /**
     * @param mixed $calm
     */
    public function setCalm($calm): void
    {
        $this->calm = $calm;
    }

    /**
     * @return mixed
     */
    public function getAngry()
    {
        return $this->angry;
    }

    /**
     * @param mixed $angry
     */
    public function setAngry($angry): void
    {
        $this->angry = $angry;
    }

    /**
     * @return mixed
     */
    public function getSad()
    {
        return $this->sad;
    }

    /**
     * @param mixed $sad
     */
    public function setSad($sad): void
    {
        $this->sad = $sad;
    }

    /**
     * @return mixed
     */
    public function getConfused()
    {
        return $this->confused;
    }

    /**
     * @param mixed $confused
     */
    public function setConfused($confused): void
    {
        $this->confused = $confused;
    }

    /**
     * @return mixed
     */
    public function getDisgusted()
    {
        return $this->disgusted;
    }

    /**
     * @param mixed $disgusted
     */
    public function setDisgusted($disgusted): void
    {
        $this->disgusted = $disgusted;
    }

    /**
     * @return mixed
     */
    public function getSurprised()
    {
        return $this->surprised;
    }

    /**
     * @param mixed $surprised
     */
    public function setSurprised($surprised): void
    {
        $this->surprised = $surprised;
    }

    /**
     * @return mixed
     */
    public function getHappy()
    {
        return $this->happy;
    }

    /**
     * @param mixed $happy
     */
    public function setHappy($happy): void
    {
        $this->happy = $happy;
    }

    /**
     * @return mixed
     */
    public function getFear()
    {
        return $this->fear;
    }

    /**
     * @param mixed $fear
     */
    public function setFear($fear): void
    {
        $this->fear = $fear;
    }
}

<?php

namespace App\Entity;

use App\Repository\FaceShotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FaceShotRepository::class)
 */
class FaceShot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"answer_face_shots"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"answer_face_shots","answer_face_shots_emotion_per_photo"})
     */
    private $image;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"answer_face_shots"})
     */
    private $images = [];

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $calm;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $angry;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $sad;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $confused;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $disgusted;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $surprised;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $happy;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $fear;

    /**
     * @ORM\ManyToOne(targetEntity=Answer::class, inversedBy="faceShots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $answer;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $faceshotTimestamp;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"answer_face_shots_emotion_per_photo"})
     */
    private $faceshotNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        if ($this->image !== null && file_exists($this->image)) {
            $imageContent = file_get_contents($this->image);
            return 'data:image/jpeg;base64,' . base64_encode($imageContent);
        }
        return null;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function setAnswer(?Answer $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJoy()
    {
        return $this->joy;
    }

    /**
     * @param mixed $joy
     */
    public function setJoy($joy): void
    {
        $this->joy = $joy;
    }

    /**
     * @return mixed
     */
    public function getSorrow()
    {
        return $this->sorrow;
    }

    /**
     * @param mixed $sorrow
     */
    public function setSorrow($sorrow): void
    {
        $this->sorrow = $sorrow;
    }

    /**
     * @return mixed
     */
    public function getAnger()
    {
        return $this->anger;
    }

    /**
     * @param mixed $anger
     */
    public function setAnger($anger): void
    {
        $this->anger = $anger;
    }

    /**
     * @return mixed
     */
    public function getSurprise()
    {
        return $this->surprise;
    }

    /**
     * @param mixed $surprise
     */
    public function setSurprise($surprise): void
    {
        $this->surprise = $surprise;
    }

    /**
     * @return string|null
     */
    public function getFaceshotTimestamp(): ?string
    {
        return $this->faceshotTimestamp ? $this->faceshotTimestamp->format('H:i:s') : null;
    }

    /**
     * @param mixed $faceshotTimestamp
     */
    public function setFaceshotTimestamp($faceshotTimestamp): self
    {
        $this->faceshotTimestamp = $faceshotTimestamp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCalm()
    {
        return $this->truncateToTwoDecimals($this->calm);
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
        return $this->truncateToTwoDecimals($this->angry);
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
        return $this->truncateToTwoDecimals($this->sad);
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
        return $this->truncateToTwoDecimals($this->confused);
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
        return $this->truncateToTwoDecimals($this->disgusted);
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
        return $this->truncateToTwoDecimals($this->surprised);
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
        return $this->truncateToTwoDecimals($this->happy);
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
        return $this->truncateToTwoDecimals($this->fear);
    }

    /**
     * @param mixed $fear
     */
    public function setFear($fear): void
    {
        $this->fear = $fear;
    }

    /**
     * @return mixed
     */
    public function getFaceshotNumber(): string
    {
        return $this->faceshotNumber;
    }

    /**
     * @param mixed $faceshotNumber
     */
    public function setFaceshotNumber($faceshotNumber): void
    {
        $this->faceshotNumber = $faceshotNumber;
    }

    private function truncateToTwoDecimals($number)
    {
        return substr($number, 0, strpos($number, '.') + 3);
    }
}

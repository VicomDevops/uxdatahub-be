<?php

namespace App\Entity;

use App\Repository\SentenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SentenceRepository::class)
 */
class Sentence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"google_step", "google_tester"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"google_step", "google_tester"})
     */
    private $content;

    /**
     * @ORM\Column(type="float")
     * @Groups({"google_step", "google_tester"})
     */
    private $magnitude;

    /**
     * @ORM\Column(type="float")
     * @Groups({"google_step", "google_tester"})
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity=Answer::class, inversedBy="sentences")
     */
    private $answer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMagnitude(): ?float
    {
        return $this->magnitude;
    }

    public function setMagnitude(float $magnitude): self
    {
        $this->magnitude = $magnitude;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

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
}

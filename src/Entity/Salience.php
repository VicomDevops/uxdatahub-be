<?php

namespace App\Entity;

use App\Repository\SalienceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SalienceRepository::class)
 */
class Salience
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"google_step", "google_tester"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"google_step", "google_tester"})
     */
    private $word;

    /**
     * @ORM\Column(type="float")
     * @Groups({"google_step", "google_tester"})
     */
    private $salience;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"google_step", "google_tester"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Answer::class, inversedBy="saliences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $answer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;

        return $this;
    }

    public function getSalience(): ?float
    {
        return $this->salience;
    }

    public function setSalience(float $salience): self
    {
        $this->salience = $salience;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

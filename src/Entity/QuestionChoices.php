<?php

namespace App\Entity;

use App\Repository\QuestionChoicesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=QuestionChoicesRepository::class)
 */
class QuestionChoices
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"view_scenario"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $choice1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $choice2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $choice3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $choice4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)*
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $choice5;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $choice6;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $minScale;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $maxScale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $borneInf;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_step", "view_scenario", "get_test"})
     */
    private $borneSup;

    /**
     * @ORM\OneToMany(targetEntity=Step::class, mappedBy="questionChoices")
     */
    private $steps;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoice1(): ?string
    {
        return $this->choice1;
    }

    public function setChoice1(?string $choice1): self
    {
        $this->choice1 = $choice1;

        return $this;
    }

    public function getChoice2(): ?string
    {
        return $this->choice2;
    }

    public function setChoice2(?string $choice2): self
    {
        $this->choice2 = $choice2;

        return $this;
    }

    public function getChoice3(): ?string
    {
        return $this->choice3;
    }

    public function setChoice3(?string $choice3): self
    {
        $this->choice3 = $choice3;

        return $this;
    }

    public function getChoice4(): ?string
    {
        return $this->choice4;
    }

    public function setChoice4(?string $choice4): self
    {
        $this->choice4 = $choice4;

        return $this;
    }

    public function getChoice5(): ?string
    {
        return $this->choice5;
    }

    public function setChoice5(?string $choice5): self
    {
        $this->choice5 = $choice5;

        return $this;
    }

    public function getChoice6(): ?string
    {
        return $this->choice6;
    }

    public function setChoice6(?string $choice6): self
    {
        $this->choice6 = $choice6;

        return $this;
    }

    public function getMinScale(): ?int
    {
        return $this->minScale;
    }

    public function setMinScale(?int $minScale): self
    {
        $this->minScale = $minScale;

        return $this;
    }

    public function getMaxScale(): ?int
    {
        return $this->maxScale;
    }

    public function setMaxScale(?int $maxScale): self
    {
        $this->maxScale = $maxScale;

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
            $step->setQuestionChoices($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
            // set the owning side to null (unless already changed)
            if ($step->getQuestionChoices() === $this) {
                $step->setQuestionChoices(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBorneInf()
    {
        return $this->borneInf;
    }

    /**
     * @param mixed $borneInf
     */
    public function setBorneInf($borneInf): void
    {
        $this->borneInf = $borneInf;
    }

    /**
     * @return mixed
     */
    public function getBorneSup()
    {
        return $this->borneSup;
    }

    /**
     * @param mixed $borneSup
     */
    public function setBorneSup($borneSup): void
    {
        $this->borneSup = $borneSup;
    }


}

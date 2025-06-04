<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AnswersDto
{
    /**
     * @var string
     * @Assert\NotBlank
     */
    private $state;

    /**
     * @var array
     * @Assert\Type("array")
     * @Assert\NotBlank
     */
    private $answers;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $finishedAt;

    // Getter and Setter methods for each property

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getAnswers(): ?array
    {
        return $this->answers;
    }

    public function setAnswers(?array $answers): self
    {
        $this->answers = $answers;

        return $this;
    }

    public function getFinishedAt(): ?string
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?string $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

}
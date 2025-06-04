<?php

namespace App\Entity;

use App\Repository\LicenceTypeRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=LicenceTypeRepository::class)
 * @UniqueEntity("title")
 */
class LicenceCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"buy_licence"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"edit_type","create_type","buy_licence"})
     
     */
    private $title;
    

     /**
     * @ORM\OneToMany(targetEntity=Contract::class, mappedBy="licenceCategory", cascade={"persist","remove"})
     */
    private $contracts;

    /**
     * @ORM\Column(type="float")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isMultiPlatform;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isInsightPanel;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isModerateTest;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isStatistics;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isStatByStepAndByTester;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isDeep;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isNonModerateTest;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isProductServiceScenario;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isAbTest;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isSpeechToText;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isJourneyMap;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isEmptionalMap;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $isProfileTypes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $subCLientsNb;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $testersNb;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"edit_type","create_type","buy_licence"})
     */
    private $clientTestersNb;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIsMultiPlatform(): ?bool
    {
        return $this->isMultiPlatform;
    }

    public function setIsMultiPlatform(bool $isMultiPlatform): self
    {
        $this->isMultiPlatform = $isMultiPlatform;

        return $this;
    }

    public function getIsInsightPanel(): ?bool
    {
        return $this->isInsightPanel;
    }

    public function setIsInsightPanel(bool $isInsightPanel): self
    {
        $this->isInsightPanel = $isInsightPanel;

        return $this;
    }

    public function getIsModerateTest(): ?bool
    {
        return $this->isModerateTest;
    }

    public function setIsModerateTest(bool $isModerateTest): self
    {
        $this->isModerateTest = $isModerateTest;

        return $this;
    }

    public function getIsStatistics(): ?bool
    {
        return $this->isStatistics;
    }

    public function setIsStatistics(bool $isStatistics): self
    {
        $this->isStatistics = $isStatistics;

        return $this;
    }

    public function getIsStatByStepAndByTester(): ?bool
    {
        return $this->isStatByStepAndByTester;
    }

    public function setIsStatByStepAndByTester(bool $isStatByStepAndByTester): self
    {
        $this->isStatByStepAndByTester = $isStatByStepAndByTester;

        return $this;
    }

    public function getIsDeep(): ?bool
    {
        return $this->isDeep;
    }

    public function setIsDeep(bool $isDeep): self
    {
        $this->isDeep = $isDeep;

        return $this;
    }

    public function getIsNonModerateTest(): ?bool
    {
        return $this->isNonModerateTest;
    }

    public function setIsNonModerateTest(bool $isNonModerateTest): self
    {
        $this->isNonModerateTest = $isNonModerateTest;

        return $this;
    }

    public function getIsProductServiceScenario(): ?bool
    {
        return $this->isProductServiceScenario;
    }

    public function setIsProductServiceScenario(bool $isProductServiceScenario): self
    {
        $this->isProductServiceScenario = $isProductServiceScenario;

        return $this;
    }

    public function getIsAbTest(): ?bool
    {
        return $this->isAbTest;
    }

    public function setIsAbTest(bool $isAbTest): self
    {
        $this->isAbTest = $isAbTest;

        return $this;
    }

    public function getIsSpeechToText(): ?bool
    {
        return $this->isSpeechToText;
    }

    public function setIsSpeechToText(bool $isSpeechToText): self
    {
        $this->isSpeechToText = $isSpeechToText;

        return $this;
    }

    public function getIsJourneyMap(): ?bool
    {
        return $this->isJourneyMap;
    }

    public function setIsJourneyMap(bool $isJourneyMap): self
    {
        $this->isJourneyMap = $isJourneyMap;

        return $this;
    }

    public function getIsEmptionalMap(): ?bool
    {
        return $this->isEmptionalMap;
    }

    public function setIsEmptionalMap(bool $isEmptionalMap): self
    {
        $this->isEmptionalMap = $isEmptionalMap;

        return $this;
    }

    public function getIsProfileTypes(): ?bool
    {
        return $this->isProfileTypes;
    }

    public function setIsProfileTypes(bool $isProfileTypes): self
    {
        $this->isProfileTypes = $isProfileTypes;

        return $this;
    }

    public function getSubCLientsNb(): ?int
    {
        return $this->subCLientsNb;
    }

    public function setSubCLientsNb(?int $subCLientsNb): self
    {
        $this->subCLientsNb = $subCLientsNb;

        return $this;
    }

    public function getTestersNb(): ?int
    {
        return $this->testersNb;
    }

    public function setTestersNb(?int $testersNb): self
    {
        $this->testersNb = $testersNb;

        return $this;
    }

    public function getClientTestersNb(): ?int
    {
        return $this->clientTestersNb;
    }

    public function setClientTestersNb(?int $clientTestersNb): self
    {
        $this->clientTestersNb = $clientTestersNb;

        return $this;
    }

    
    public function geType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    
    /**
     * @return Collection|Contract[]
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts[] = $contract;
            $contract->setLicenceCategory($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->contains($contract)) {
            $this->contracts->removeElement($contract);
        }

        return $this;
    }


}

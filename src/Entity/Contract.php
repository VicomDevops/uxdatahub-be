<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContractRepository::class)
 */
class Contract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"view_contract"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_contract","view_contract"})
     */
    private $companyToInvoice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_contract","view_contract"})
     
     */
    private $signingName;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract","view_contract"})
    
     */
    private $firstNameSignatory;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract"})
        
     */
    private $address;

    /**
     * @ORM\Column(type="string",nullable=true)
     * @Groups({"create_contract"})
        
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract"})
     
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract"})
     */
    private $invoiceEmail;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract"})
     */
    private $countryResidence;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract"})
     */
    private $identityCardFront;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_contract"})
     */
    private $identityCardBack;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_sepa_infos"})
     */
    private $numVoie;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_sepa_infos"})
     */
    private $accountOwner;


    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_sepa_infos"})
     */
    private $bankName;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_sepa_infos"})
     */
    private $IBAN;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"create_sepa_infos"})
     */
    private $codeBIC;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"view_contract"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"view_contract"})
     */
    //0: contract créer par le client ,1 : contrat docusighn pret, 
    private $startAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"view_contract"})     
     */
    private $endAt;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="contracts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

     /**
     * @ORM\ManyToOne(targetEntity=LicenceCategory::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $licenceCategory;

    public function __construct()
    {
        $this->startAt = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyToInvoice(): ?string
    {
        return $this->companyToInvoice;
    }

    public function setCompanyToInvoice(string $companyToInvoice): self
    {
        $this->companyToInvoice = $companyToInvoice;

        return $this;
    }

    public function getSigningName(): ?string
    {
        return $this->signingName;
    }

    public function setSigningName(string $signingName): self
    {
        $this->signingName = $signingName;

        return $this;
    }

    public function getFirstNameSignatory(): ?string
    {
        return $this->firstNameSignatory;
    }

    public function setFirstNameSignatory(string $firstNameSignatory): self
    {
        $this->firstNameSignatory = $firstNameSignatory;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function setZipCode(int $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getInvoiceEmail(): ?string
    {
        return $this->invoiceEmail;
    }

    public function setInvoiceEmail(string $invoiceEmail): self
    {
        $this->invoiceEmail = $invoiceEmail;

        return $this;
    }

    public function getCountryResidence(): ?string
    {
        return $this->countryResidence;
    }

    public function setCountryResidence(string $countryResidence): self
    {
        $this->countryResidence = $countryResidence;

        return $this;
    }

    public function getIdentityCardFront(): ?string
    {
        return $this->identityCardFront;
    }

    public function setIdentityCardFront(string $identityCardFront): self
    {
        $this->identityCardFront = $identityCardFront;

        return $this;
    }

    public function getIdentityCardBack(): ?string
    {
        return $this->identityCardBack;
    }

    public function setIdentityCardBack(string $identityCardBack): self
    {
        $this->identityCardBack = $identityCardBack;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getIBAN(): ?string
    {
        return $this->IBAN;
    }

    public function setIBAN(string $IBAN): self
    {
        $this->IBAN = $IBAN;

        return $this;
    }

    public function getCodeBIC(): ?string
    {
        return $this->codeBIC;
    }

    public function setCodeBIC(string $codeBIC): self
    {
        $this->codeBIC = $codeBIC;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    

    /**
     * Get the value of numVoie
     */ 
    public function getNumVoie()
    {
        return $this->numVoie;
    }

    /**
     * Set the value of numVoie
     *
     * @return  self
     */ 
    public function setNumVoie($numVoie)
    {
        $this->numVoie = $numVoie;

        return $this;
    }

    /**
     * Get the value of accountOwner
     */ 
    public function getAccountOwner()
    {
        return $this->accountOwner;
    }

    /**
     * Set the value of accountOwner
     *
     * @return  self
     */ 
    public function setAccountOwner($accountOwner)
    {
        $this->accountOwner = $accountOwner;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }
    public function __toString()
    {
        return (string)$this->getStartAt();
    }
    
    public function getLicenceCategory(): ?LicenceCategory
    {
        return $this->licenceCategory;
    }

    public function setLicenceCategory(?LicenceCategory $licenceCategory): self
    {
        $this->licenceCategory = $licenceCategory;

        return $this;
    }
}

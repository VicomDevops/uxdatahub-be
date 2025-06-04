<?php

namespace App\Entity;

use App\Repository\TesterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TesterRepository::class)
 */
class Tester extends User
{
    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @Assert\Regex("/^[a-zA-Z\s]{1,50}$/", message="Ce champ ne doit pas contenir des chiffres ou des caractères spéciaux")
     * @ORM\Column(type="string", length=100)
     * @Groups({"signup", "current_user", "update_tester","get_panel","google_step","first_signup"})
     */
    private $name;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @Assert\Regex("/^[a-zA-Z\s]{1,50}$/", message="Ce champ ne doit pas contenir des chiffres ou des caractères spéciaux")
     * @ORM\Column(type="string", length=100)
     * @Groups({"signup", "current_user", "update_tester","google_step","first_signup"})
     */
    private $lastname;

    /**
     * @Assert\Choice(choices={
     *     "Femme",
     *     "Homme"
     *      }, message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=20)
     * @Groups({"signup", "current_user", "update_tester","get_panel","first_signup"})
     */
    private $gender;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=50)
     * @Groups({"signup", "current_user", "update_tester","get_panel","first_signup"})
     */
    private $country;

    /**
     * @Assert\Choice(
     *     choices={
     *     "Agriculteurs exploitants",
     *     "Artisans",
     *     "Commerçants et chefs d’entreprise",
     *     "Cadres et professions intellectuelles supérieures",
     *     "Professions Intermédiaires",
     *     "Employés",
     *     "Ouvriers"
     *      },
     *     message="ce champ est obligatoire"
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $csp;

    /**
     * @Assert\Choice(choices={
     *     "Aucun diplôme",
     *     "Brevet des collèges, CAP, BEP ou autre",
     *     "Bac, Brevet professionnel",
     *     "Bac",
     *     "Bac +2",
     *     "Bac +3 ou 4",
     *     "Bac +5",
     *     "CAP",
     *     "BEP ou autre",
     *     "> Bac +5"
     *     }, message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=50)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $studyLevel;

    /**
     * @Assert\Choice(choices={
     *     "Activités informatiques",
     *     "Recherche et développement",
     *     "Agriculture",
     *     "Banque/assurance",
     *     "Commerce",
     *     "Education",
     *     "Fonction publique",
     *     "Industrie",
     *     "Santé",
     *     "Services",
     *     "hôtellerie/restauration",
     *     "autres"
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $activityArea;

    /**
     * @Assert\Choice(choices={
     *     "Conception",
     *     "Communication",
     *     "Production et Fabrication",
     *     "Management",
     *     "Distribution",
     *     "Finance",
     *     "Achat et Approvisionnement",
     *     "Logistique et Transport",
     *     "Contrôle et qualité",
     *     "Vente",
     *     "Ressources Humaines",
     *     "Informatique",
     *     "Juridique",
     *     "Marketing",
     *     "Infrastructure et Sécurité",
     *     "Comptabilité",
     *     "Direction et Stratégie",
     *     "Service à la clientèle",
     *     "Recherche et développement",
     *     "Non concerné"
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $department;

    /**
     * @Assert\Choice(choices={
     *     "< 800€",
     *     "Entre 800 et 1500€",
     *     "1501€ et 2000€",
     *     "Entre 2001€ et 3500€",
     *     "Entre 3501€ et 5000€",
     *     "> 5000€",
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $revenu;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $poste;

    /**
     * @Assert\Choice(choices={
     *     "Indépendant",
     *     "Non concerné",
     *     "Pas de fonction managériale",
     *     "Management d’équipe",
     *     "Responsable fonctionnel",
     *     "Direction de pôle",
     *     "Direction générale",
     *     "Assistant de direction",
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $fonctionManageriale;

    /**
     * @Assert\Choice(choices={
     *     "Non concerné",
     *     "Indépendant",
     *     "Moins de 10 salariés",
     *     "Entre 10 et 49 salariés",
     *     "Entre 50 et 99 salariés",
     *     "Entre 100 et 249 salariés",
     *     "Entre 250 et 499 salariés",
     *     "Entre 500 et 999 salariés",
     *     "Entre 100 et 4999 salariés",
     *     "Plus de 5000 salariés"
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $tailleSte;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=100)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $socialMedia;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $os;

    /**
     * @ORM\Column(type="string", length=100,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_tester"})
     */
    private $osMobile;

    /**
     * @ORM\Column(type="string", length=100,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_tester"})
     */
    private $osTablet;


    /**
     * @Assert\Choice(choices={
     *     "Célibataire",
     *     "Couple",
     *     "Famille sans enfants",
     *     "Famille avec enfants",
     *     "Marié(e)",
     *     "Divorcé(e)",
     *     "Veuf(ve)",
     *     "Pacsé(e)",
     *     "Séparé(e)"
     *      })
     * @ORM\Column(type="string", length=50)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $maritalStatus;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @Assert\Range(
     *     min="-65 years",
     *     max="-16 years"
     * )
     * @ORM\Column(type="date")
     * @Groups({"signup", "current_user","first_signup"})
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $phone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $langue;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup", "current_user", "update_tester","first_signup"})
     */
    private $city;

    /**
     * @Assert\Choice(choices={
     *     "Au moins 1 fois par mois",
     *     "Au moins 1 fois tous les 3 mois",
     *     "Au moins 1 fois tous les 6 mois",
     *     " Au moins 1 fois par an",
     *     "Jamais"
     *      })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $internetFrequency;

    /**
     * @Assert\Choice(choices={
     *     "1",
     *     "0",
     *      })
     * @ORM\Column(type="integer",nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $achatInternet;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeId;

    /**
     * @ORM\OneToMany(targetEntity=Test::class, mappedBy="tester")
     */
    private $tests;

    /**
     * @Assert\Choice(choices={
     *     "Moins de 5 minutes",
     *     "5 à 30 minutes",
     *     "30 à 60 minute",
     *     "1-3 heures",
     *     "3 heures et plus",
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $temps_passe;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     * @Groups({"signup","update_tester","first_signup"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"update_tester"})
     */
    private $identityCardFront;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"signup","update_tester"})
     */
    private $identityCardBack;

    /**
     * @ORM\ManyToMany(targetEntity=Panel::class, cascade={"persist", "remove"},inversedBy="insightTesters")
     */
    private $panelsInsight;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"signup", "current_user"})
     */
    private $privacyPolicy;

    /**
     * @ORM\Column(type="string",nullable=true)
     * @Groups({"signup", "current_user","first_signup"})
     */
    private $cgu;

    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $device;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     * @Groups({"update_tester","current_user"})
     */
    private $profileInformations;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     * @Groups({"update_tester","current_user"})
     */
    private $testVisio;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     * @Groups({"update_tester","current_user"})
     */
    private $viaEmail;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     * @Groups({"update_tester","current_user"})
     */
    private $sms;

    /**
     * @ORM\Column(type="integer",nullable=true)
     * @Groups({"signup", "current_user", "update_tester"})
     */
    private $completionRate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"signup","current_user", "profile_image_tester"})
     */
    private $profileImage;


    public function __construct()
    {
        $this->tests = new ArrayCollection();
        $this->panelsInsight = new ArrayCollection();
        $this->privacyPolicy=null;
        $this->cgu=null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCsp(): ?string
    {
        return $this->csp;
    }

    public function setCsp(string $csp): self
    {
        $this->csp = $csp;

        return $this;
    }

    public function getStudyLevel(): ?string
    {
        return $this->studyLevel;
    }

    public function setStudyLevel(string $studyLevel): self
    {
        $this->studyLevel = $studyLevel;

        return $this;
    }

    public function getSocialMedia(): ?string
    {
        return $this->socialMedia;
    }

    public function setSocialMedia(string $socialMedia): self
    {
        $this->socialMedia = $socialMedia;

        return $this;
    }

    public function getOs(): ?string
    {
//        $os = $this->os;
        return $this->os;
//        return array_unique($os);
    }

    public function setOs(string $os): self
    {
        $this->os = $os;
        return $this;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(string $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(?string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->tests->contains($test)) {
            $this->tests[] = $test;
            $test->setTester($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getTester() === $this) {
                $test->setTester(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActivityArea()
    {
        return $this->activityArea;
    }

    /**
     * @param mixed $activityArea
     */
    public function setActivityArea($activityArea): void
    {
        $this->activityArea = $activityArea;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department): void
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getRevenu()
    {
        return $this->revenu;
    }

    /**
     * @param mixed $revenu
     */
    public function setRevenu($revenu): void
    {
        $this->revenu = $revenu;
    }

    /**
     * @return mixed
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * @param mixed $poste
     */
    public function setPoste($poste): void
    {
        $this->poste = $poste;
    }

    /**
     * @return mixed
     */
    public function getFonctionManageriale()
    {
        return $this->fonctionManageriale;
    }

    /**
     * @param mixed $fonctionManageriale
     */
    public function setFonctionManageriale($fonctionManageriale): void
    {
        $this->fonctionManageriale = $fonctionManageriale;
    }

    /**
     * @return mixed
     */
    public function getTailleSte()
    {
        return $this->tailleSte;
    }

    /**
     * @param mixed $tailleSte
     */
    public function setTailleSte($tailleSte): void
    {
        $this->tailleSte = $tailleSte;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return mixed
     */
    public function getLangue()
    {
        return $this->langue;
    }

    /**
     * @param mixed $langue
     */
    public function setLangue($langue): void
    {
        $this->langue = $langue;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getTempsPasse()
    {
        return $this->temps_passe;
    }

    /**
     * @param mixed $temps_passe
     */
    public function setTempsPasse($temps_passe): void
    {
        $this->temps_passe = $temps_passe;
    }

    /**
     * @return mixed
     */
    public function getInternetFrequency()
    {
        return $this->internetFrequency;
    }

    /**
     * @param mixed $internetFrequency
     */
    public function setInternetFrequency($internetFrequency): void
    {
        $this->internetFrequency = $internetFrequency;
    }

    /**
     * @return mixed
     */
    public function getAchatInternet()
    {
        return $this->achatInternet;
    }

    /**
     * @param mixed $achatInternet
     */
    public function setAchatInternet($achatInternet): void
    {
        $this->achatInternet = $achatInternet;
    }

    /**
     * @return Collection|Panel[]
     */
    public function getPanels(): Collection
    {
        return $this->panels;
    }

    public function addPanel(Panel $panel): self
    {
        if (!$this->panelsInsight->contains($panel)) {
            $this->panelsInsight[] = $panel;
        }

        return $this;
    }

    public function removePanel(Panel $panel): self
    {
        if ($this->panelsInsight->contains($panel)) {
            $this->panelsInsight->removeElement($panel);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOsMobile()
    {
        return $this->osMobile;
    }

    /**
     * @param mixed $osMobile
     */
    public function setOsMobile($osMobile): void
    {
        $this->osMobile = $osMobile;
    }

    /**
     * @return mixed
     */
    public function getOsTablet()
    {
        return $this->osTablet;
    }

    /**
     * @param mixed $osTablet
     */
    public function setOsTablet($osTablet): void
    {
        $this->osTablet = $osTablet;
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

        /**
     * Get the value of privacyPolicy
     */ 
    public function getPrivacyPolicy()
    {
        return $this->privacyPolicy;
    }

    /**
     * Set the value of privacyPolicy
     *
     * @return  self
     */ 
    public function setPrivacyPolicy($privacyPolicy)
    {
        $this->privacyPolicy = $privacyPolicy;

        return $this;
    }

    /**
     * Get the value of cgu
     */ 
    public function getCgu()
    {
        return $this->cgu;
    }

    /**
     * Set the value of cgu
     *
     * @return  self
     */ 
    public function setCgu($cgu)
    {
        $this->cgu = $cgu;

        return $this;
    }

    /**
     * Get the value of device
     */ 
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set the value of device
     *
     * @return  self
     */ 
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get the value of profileInformations
     */ 
    public function getProfileInformations()
    {
        return $this->profileInformations;
    }

    /**
     * Set the value of profileInformations
     *
     * @return  self
     */ 
    public function setProfileInformations($profileInformations)
    {
        $this->profileInformations = $profileInformations;

        return $this;
    }

    /**
     * Get the value of testVisio
     */ 
    public function getTestVisio()
    {
        return $this->testVisio;
    }

    /**
     * Set the value of testVisio
     *
     * @return  self
     */ 
    public function setTestVisio($testVisio)
    {
        $this->testVisio = $testVisio;

        return $this;
    }

    /**
     * Get the value of sms
     */ 
    public function getSms()
    {
        return $this->sms;
    }

    /**
     * Set the value of sms
     *
     * @return  self
     */ 
    public function setSms($sms)
    {
        $this->sms = $sms;

        return $this;
    }

    /**
     * Get the value of viaEmail
     */ 
    public function getViaEmail()
    {
        return $this->viaEmail;
    }

    /**
     * Set the value of viaEmail
     *
     * @return  self
     */ 
    public function setViaEmail($viaEmail)
    {
        $this->viaEmail = $viaEmail;

        return $this;
    }

    /**
     * Get the value of completionRate
     */ 
    public function getCompletionRate()
    {
        return $this->completionRate;
    }

    /**
     * Set the value of completionRate
     *
     * @return  self
     */ 
    public function setCompletionRate($completionRate)
    {
        $this->completionRate = $completionRate;

        return $this;
    }

    /**
     * Get the value of profileImage
     */ 
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * Set the value of profileImage
     *
     * @return  self
     */ 
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;

        return $this;
    }

  public function getUserIdentifier(): string
    {
        return $this->email;
    }
}

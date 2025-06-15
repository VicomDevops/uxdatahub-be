<?php

namespace App\Entity;

use App\Repository\ClientTesterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientTesterRepository::class)
 */
class ClientTester extends User
{
    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"current_user","get_panel","google_step","update_client_tester","data_by_scenario","tester_video","video_answer_tester","panel_details","face_reco_scenario","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"current_user","google_step","update_client_tester","get_panel","data_by_scenario","tester_video","video_answer_tester","panel_details","face_reco_scenario","answer_face_shots_emotion_per_photo","targeted_recommendations"})
     */
    private $lastname;

    /**
     * @ORM\ManyToMany(targetEntity=Panel::class, inversedBy="clientTesters")
     */
    private $panels;

    /**
     * @ORM\OneToMany(targetEntity=Test::class, mappedBy="clientTester")
     */
    private $tests;

    /**
     * @Assert\Choice(choices={
     *     "Femme",
     *     "Homme"
     *      }, message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=20,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_client_tester","get_panel","panel_details"})
     */
    private $gender;
    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @ORM\Column(type="string", length=50,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_client_tester","get_panel","panel_details"})
     */
    private $country;

    /**
     * @Assert\Choice(
     *     choices={
     *     "Agriculteurs exploitants",
     *     "Artisans, commerçants et chefs d’entreprise",
     *     "Cadres et professions intellectuelles supérieures",
     *     "Professions Intermédiaires",
     *     "Employés",
     *     "Ouvriers"
     *      },
     *     message="ce champ est obligatoire"
     * )
     * @ORM\Column(type="string", length=255,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_client_tester"})
     */
    private $csp;
    /**
     * @ORM\Column(type="string", length=100,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_client_tester"})
     */
    private $os;

    /**
     * @Assert\NotBlank(message="ce champ est obligatoire")
     * @Assert\Range(
     *     min="-65 years",
     *     max="-16 years"
     * )
     * @ORM\Column(type="date",nullable=true,options={"default"= null})
     * @Groups({"current_user","update_client_tester"})
     */
    private $dateOfBirth;
    /**
     * @ORM\Column(type="string", length=30,nullable=true,options={"default"= null})
     * @Groups({"signup", "current_user", "update_client_tester"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=100,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_client_tester"})
     */
    private $osMobile;

    /**
     * @ORM\Column(type="string", length=100,nullable=true,options={"default"= null})
     * @Groups({"current_user", "update_client_tester"})
     */
    private $osTablet;
    
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup", "current_user", "update_client_tester"})
     */
    private $socialMedia;

    
    /**
     * @Assert\Choice(choices={
     *     "Célibataire",
     *     "Couple",
     *     "Famille sans enfants",
     *     "Famille avec enfants",
     *      })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_client_tester"})
     */
    private $maritalStatus;

    /**
     * @Assert\Choice(choices={
     *     "Aucun diplôme",
     *     "Brevet des collèges",
     *     "Brevet professionnel",
     *     "Bac",
     *     "Bac +2",
     *     "Bac +3/5",
     *     "Bac +5",
     *     "CAP",
     *     "BEP ou autre",
     *     "> Bac +5"
     *     })
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"signup", "current_user", "update_client_tester"})
     */
    private $studyLevel;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup", "current_user", "update_client_tester"})
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup","update_client_tester","current_user"})
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"signup","update_client_tester","current_user"})
     */
    private $adresse;


     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"signup","update_client_tester","current_user"})
     */
    private $profileImage;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="clientTester")
     */
    private $answers;


    /**
     * @ORM\OneToMany(targetEntity=Notifications::class, mappedBy="clientTester", cascade={"persist"})
     */
    private Collection $notifications;


    public function __construct()
    {
        $this->panels = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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

    /**
     * @return Collection|Panel[]
     */
    public function getPanels(): Collection
    {
        return $this->panels;
    }

    public function addPanel(Panel $panel): self
    {
        if (!$this->panels->contains($panel)) {
            $this->panels[] = $panel;
        }

        return $this;
    }

    public function removePanel(Panel $panel): self
    {
        if ($this->panels->contains($panel)) {
            $this->panels->removeElement($panel);
        }

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
            $test->setClientTester($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getClientTester() === $this) {
                $test->setClientTester(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): self
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCsp()
    {
        return $this->csp;
    }

    /**
     * @param mixed $csp
     */
    public function setCsp($csp): self
    {
        $this->csp = $csp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @param mixed $os
     */
    public function setOs($os): self
    {
        $this->os = $os;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): self
    {
        $this->phone = $phone;
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

    /**
     * Get "Célibataire",
     */ 
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * Set "Célibataire",
     *
     * @return  self
     */ 
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    /**
     * Get the value of socialMedia
     */ 
    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    /**
     * Set the value of socialMedia
     *
     * @return  self
     */ 
    public function setSocialMedia($socialMedia)
    {
        $this->socialMedia = $socialMedia;

        return $this;
    }

    /**
     * Get "Aucun diplôme",
     */ 
    public function getStudyLevel()
    {
        return $this->studyLevel;
    }

    /**
     * Set "Aucun diplôme",
     *
     * @return  self
     */ 
    public function setStudyLevel($studyLevel)
    {
        $this->studyLevel = $studyLevel;

        return $this;
    }
    public function __toString() {
        return $this->name;
    }

    /**
     * Get the value of city
     */ 
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the value of city
     *
     * @return  self
     */ 
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of postalCode
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set the value of postalCode
     *
     * @return  self
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get the value of adresse
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set the value of adresse
     *
     * @return  self
     */
    public function setAdresse($adresse):self
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get the value of profileImage
     */ 
    public function getProfileImage()
    {
        return  $this->profileImage;
    }

    /**
     * Set the path of profileImage
     *
     * @return  self
     */
    public function setProfileImage(string $profileImage)
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setClientTester($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getClientTester() === $this) {
                $answer->setClientTester(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Notifications>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotifications(Notifications $notifications): static
    {
        if (!$this->notifications->contains($notifications)) {
            $this->notifications->add($notifications);
            $notifications->setClientTester($this);
        }

        return $this;
    }

    public function removeNotifications(Notifications $notifications): static
    {
        if ($this->notifications->removeElement($notifications)) {
            // set the owning side to null (unless already changed)
            if ($notifications->getClientTester() === $this) {
                $notifications->setClientTester(null);
            }
        }

        return $this;
    }
}

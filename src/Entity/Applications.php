<?php

namespace App\Entity;

use App\Repository\ApplicationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JsonSerializable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ApplicationsRepository::class)
 * @Gedmo\Loggable
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="census_applications")
 * 
 */
class Applications implements JsonSerializable
{
    const LANGUAGES = ["Mandinka", "Wollof", "Balante", "Fula", "Jola", "Sarahule", "Serere", "Creole/Aku Marabout", "Manjago"];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Gedmo\Versioned
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=40,unique=true)
     *  @Gedmo\Versioned
     */
    private $nin;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Gedmo\Versioned
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $cv;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $score;

    /** 
     * @ORM\Column(type="boolean", nullable=true, name="selectionner")
     */
    private $isSelected;

    /**
     * @ORM\Column(type="boolean", nullable=true, name="affecter")
     */
    private $isAffected;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $confirmation;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $captcha;

    /**
     * @ORM\ManyToOne(targetEntity=Lgas::class, inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lga;

    /**
     * @ORM\ManyToOne(targetEntity=Districts::class, inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $district;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $submission_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $middlename;

    /**
     * @ORM\Column(type="datetime")
     */
    private $birthDate;

    /**
     * @ORM\ManyToOne(targetEntity=Districts::class, inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $temporal_district_residence;

    /**
     * @ORM\ManyToOne(targetEntity=Districts::class, inversedBy="application_usual_resid")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usual_district_residence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $current_address;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone2;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $diploma;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $language1;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $language2;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $language3;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $computer_knowledge;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $use_of_tablet;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $census_or_survey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diplomaFile;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nic_copy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $certificate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $certificateFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $experienceFile;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="applications", cascade={"persist", "remove"})
     */
    private $account;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $whatsappPhone;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $sex;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $census_or_servey_certificateFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $census_or_survey_certificateFile_2;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $ipAddress;

    /**
     * @ORM\ManyToOne(targetEntity=Districts::class, inversedBy="workApplications")
     */
    private $workDistrict;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $onWaitingList;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbr_census;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $notificationSubmissionSendAt;

    public function __construct()
    {
        $this->isSelected = false;
        $this->isAffected = false;
        // $this->createdAt =  new \DateTimeInterface();
        // $this->updateAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNin(): ?string
    {
        return $this->nin;
    }

    public function setNin(string $nin): self
    {
        $this->nin = $nin;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function setLanguage1(string $language1): self
    {
        if (!in_array($language1, self::LANGUAGES)) {
            throw new \InvalidArgumentException("Invalid language $language1 choose one of " . join(',', self::LANGUAGES));
        }
        $this->language1 = $language1;

        return $this;
    }

    public function setLanguage2(?string $language2): self
    {
        if (!in_array($language2, self::LANGUAGES) && !empty($language2)) {
            throw new \InvalidArgumentException("Invalid language2 <$language2> choose one of " . join(',', self::LANGUAGES));
        }
        $this->language2 = $language2;

        return $this;
    }

    public function setLanguage3(?string $language3): self
    {
        if (!in_array($language3, self::LANGUAGES) && !empty($language3)) {
            throw new \InvalidArgumentException("Invalid language3 $language3 choose one of " . join(',', self::LANGUAGES));
        }
        $this->language3 = $language3;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'submission_number' => $this->submission_number,
            'lga' => $this->lga,
            'district' => $this->district,
            'workDistrict' => $this->workDistrict,
            'usualDistrictName' => $this->usual_district_residence != NULL ? $this->getUsualDistrictResidence()->getName() : 'Aucun',
            'temporalDistrictName' => $this->temporal_district_residence != NULL ? $this->getTemporalDistrictResidence()->getName() : 'Aucun',
            'usualDistrict' => $this->getUsualDistrictResidence(),
            'temporalDistrict' => $this->getTemporalDistrictResidence(),
            'surname' => $this->surname,
            'name' => $this->name,
            'middlename' => $this->middlename,

            'candidate' => ($this->name . ' ' . $this->middlename . ' ' . $this->surname),
            'birthDate' => $this->birthDate == NULL ? '' : $this->birthDate->format('d-m-Y'),
            'TbirthDate' => $this->birthDate == NULL ? '' : $this->birthDate->format('Y-m-d'),
            'current_address' => $this->current_address,
            'phone' => $this->phone,
            'phone2' => $this->phone2,
            'phone3' => $this->phone3,
            'whatsappPhone' => $this->whatsappPhone,
            'nin' => $this->nin,
            'diploma' => $this->diploma,
            'profession' => $this->profession,
            'language1' => $this->language1,
            'language2' => $this->language2,
            'language3' => $this->language3,
            'computer_knowledge' => $this->computer_knowledge,
            'use_of_tablet' => $this->use_of_tablet,
            'census_or_survey' => $this->census_or_survey,
            'nbr_census' => $this->nbr_census,
            'email' => $this->email,
            'isSelected' => $this->isSelected,
            'isAffected' => $this->isAffected,
            'score' => $this->score,
            'createdAt' => $this->createdAt ? $this->createdAt->format('d-m-Y H:i:s') : "",
            'sex' => $this->sex,
            'confirmation' => $this->confirmation,
            'certificateFile' => $this->certificateFile != NULL,
            'cv' => $this->cv != NULL,
            'age' => $this->getAge(),
            'candidateSelectedList' => $this->getWichListIsCandidat(),
        );
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): self
    {
        $this->cv = $cv;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score != NULL ? $this->score : $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;
        return $this;
    }
    public function getIsSelected(): ?bool
    {
        return $this->isSelected;
    }

    public function setIsSelected(bool $isSelected): self
    {
        $this->isSelected = $isSelected;

        return $this;
    }

    public function getIsAffected(): ?bool
    {
        return $this->isAffected;
    }

    public function setIsAffected(?bool $isAffected): self
    {
        $this->isAffected = $isAffected;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * ORM\PrePersist
     */
    public function prePersistStaff()
    {
        $this->setScore($this->calculScore());
    }

    // /**
    //  * ORM\PreUpdate
    //  */
    // public function doPreUpdateStaff(PreUpdateEventArgs $eventArgs)
    // {
    //     $this->setUpdateAt(new \DateTime());
    // }

    public function isConfirmation(): ?bool
    {
        return $this->confirmation;
    }

    public function setConfirmation(bool $confirmation): self
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    public function calculScore()
    {
        $myScore = 0;

        //Diplome
        switch ($this->diploma) {
            case 'Bachelor':
                $myScore += 25;
                break;
            case 'BSc':
                $myScore += 15;
                break;
            case 'BA':
                $myScore += 15;
                break;
            case 'BBA':
                $myScore += 10;
                break;

            default:
                $myScore += 0;
                break;
        }
        // attestation GBOS
        $myScore += (($this->diplomaFile != NULL) ? 25  : 0);

        $census_or_survey_experience =  array_filter([$this->census_or_servey_certificateFile, $this->census_or_survey_certificateFile_2], function ($attes) {
            return  $attes != NULL;
        });


        $nbrCensusFile = count($census_or_survey_experience);

        // experience
        if ($this->census_or_survey && in_array($this->nbr_census,  [1, 2])) {
            $myScore += ($this->nbr_census == 2 ? 25 : 20);
        }

        if ($this->nbr_census == $nbrCensusFile) {
            $myScore += ($this->nbr_census == 2 ? 25 : 20);
        }

        return $myScore;
    }

    public function getCaptcha(): ?string
    {
        return $this->captcha;
    }

    public function setCaptcha(?string $captcha): self
    {
        $this->captcha = $captcha;

        return $this;
    }

    public function getAge(): ?int
    {
        $today = new \DateTime();
        $diff = $today->diff(($this->birthDate != NULL) ? $this->birthDate : new \DateTime());
        $age = (int) $diff->format('%y');
        return $age;
    }

    public function getWichListIsCandidat(): ?string
    {
        if ($this->isSelected != NULL && $this->isSelected) {
            return 'Main List';
        } else if ($this->onWaitingList != NULL && $this->onWaitingList) {
            return "Waiting List";
        } else {
            return 'None';
        }
    }

    public function getLga(): ?Lgas
    {
        return $this->lga;
    }

    public function setLga(?Lgas $lga): self
    {
        $this->lga = $lga;

        return $this;
    }

    public function getDistrict(): ?Districts
    {
        return $this->district;
    }

    public function setDistrict(?Districts $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getSubmissionNumber(): ?string
    {
        return $this->submission_number;
    }

    public function setSubmissionNumber(string $submission_number): self
    {
        $this->submission_number = $submission_number;

        return $this;
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    public function setMiddlename(?string $middlename): self
    {
        $this->middlename = $middlename;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getTemporalDistrictResidence(): ?Districts
    {
        return $this->temporal_district_residence;
    }

    public function setTemporalDistrictResidence(?Districts $temporal_district_residence): self
    {
        $this->temporal_district_residence = $temporal_district_residence;

        return $this;
    }

    public function getUsualDistrictResidence(): ?Districts
    {
        return $this->usual_district_residence;
    }

    public function setUsualDistrictResidence(?Districts $usual_district_residence): self
    {
        $this->usual_district_residence = $usual_district_residence;

        return $this;
    }

    public function getCurrentAddress(): ?string
    {
        return $this->current_address;
    }

    public function setCurrentAddress(?string $current_address): self
    {
        $this->current_address = $current_address;

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

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getDiploma(): ?string
    {
        return $this->diploma;
    }

    public function setDiploma(?string $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }

    public function getLanguage1(): ?string
    {
        return $this->language1;
    }

    public function getLanguage2(): ?string
    {
        return $this->language2;
    }

    public function getLanguage3(): ?string
    {
        return $this->language3;
    }

    public function isComputerKnowledge(): ?bool
    {
        return $this->computer_knowledge;
    }

    public function setComputerKnowledge(?bool $computer_knowledge): self
    {
        $this->computer_knowledge = $computer_knowledge;

        return $this;
    }

    public function isUseOfTablet(): ?bool
    {
        return $this->use_of_tablet;
    }

    public function setUseOfTablet(?bool $use_of_tablet): self
    {
        $this->use_of_tablet = $use_of_tablet;

        return $this;
    }

    public function isCensusOrSurvey(): ?bool
    {
        return $this->census_or_survey;
    }

    public function setCensusOrSurvey(?bool $census_or_survey): self
    {
        $this->census_or_survey = $census_or_survey;

        return $this;
    }

    public function getDiplomaFile(): ?string
    {
        return $this->diplomaFile;
    }

    public function setDiplomaFile(?string $diplomaFile): self
    {
        $this->diplomaFile = $diplomaFile;

        return $this;
    }

    public function getNicCopy(): ?string
    {
        return $this->nic_copy;
    }

    public function setNicCopy(string $nic_copy): self
    {
        $this->nic_copy = $nic_copy;

        return $this;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(?string $certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }

    public function getCertificateFile(): ?string
    {
        return $this->certificateFile;
    }

    public function setCertificateFile(?string $certificateFile): self
    {
        $this->certificateFile = $certificateFile;

        return $this;
    }

    public function getExperienceFile(): ?string
    {
        return $this->experienceFile;
    }

    public function setExperienceFile(?string $experienceFile): self
    {
        $this->experienceFile = $experienceFile;

        return $this;
    }

    public function getAccount(): ?User
    {
        return $this->account;
    }

    public function setAccount(?User $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getWhatsappPhone(): ?string
    {
        return $this->whatsappPhone;
    }

    public function setWhatsappPhone(?string $whatsappPhone): self
    {
        $this->whatsappPhone = $whatsappPhone;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(?string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getCensusOrServeyCertificateFile(): ?string
    {
        return $this->census_or_servey_certificateFile;
    }

    public function setCensusOrServeyCertificateFile(?string $census_or_servey_certificateFile): self
    {
        $this->census_or_servey_certificateFile = $census_or_servey_certificateFile;

        return $this;
    }

    public function getCensusOrSurveyCertificateFile2(): ?string
    {
        return $this->census_or_survey_certificateFile_2;
    }

    public function setCensusOrSurveyCertificateFile2(?string $census_or_survey_certificateFile_2): self
    {
        $this->census_or_survey_certificateFile_2 = $census_or_survey_certificateFile_2;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getWorkDistrict(): ?Districts
    {
        return $this->workDistrict;
    }

    public function setWorkDistrict(?Districts $workDistrict): self
    {
        $this->workDistrict = $workDistrict;

        return $this;
    }

    public function isOnWaitingList(): ?bool
    {
        return $this->onWaitingList;
    }

    public function setOnWaitingList(?bool $onWaitingList): self
    {
        $this->onWaitingList = $onWaitingList;

        return $this;
    }

    public function getPhone3(): ?string
    {
        return $this->phone3;
    }

    public function setPhone3(?string $phone3): self
    {
        $this->phone3 = $phone3;

        return $this;
    }

    public function getNbrCensus(): ?int
    {
        return $this->nbr_census;
    }

    public function setNbrCensus(?int $nbr_census): self
    {
        $this->nbr_census = $nbr_census;

        return $this;
    }

    public function getNotificationSubmissionSendAt(): ?\DateTimeInterface
    {
        return $this->notificationSubmissionSendAt;
    }

    public function setNotificationSubmissionSendAt(?\DateTimeInterface $notificationSubmissionSendAt): self
    {
        $this->notificationSubmissionSendAt = $notificationSubmissionSendAt;

        return $this;
    }

}

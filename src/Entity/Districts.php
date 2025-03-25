<?php

namespace App\Entity;

use App\Repository\DistrictsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=DistrictsRepository::class)
 * @ORM\Table(name="census_districts")
 * @Gedmo\Loggable
 */

class Districts implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $dcode;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\ManyToOne(targetEntity=Lgas::class, inversedBy="districts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lga;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $fdcode;

    /**
     * @ORM\OneToMany(targetEntity=Wards::class, mappedBy="district")
     */
    private $wards;

    /**
     * @ORM\OneToMany(targetEntity=Applications::class, mappedBy="district")
     */
    private $applications;

    /**
     * @ORM\OneToMany(targetEntity=Applications::class, mappedBy="usual_district_residence")
     */
    private $application_usual_resid;

    /**
     * @ORM\OneToMany(targetEntity=Applications::class, mappedBy="workDistrict")
     */
    private $workApplications;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="district")
     */
    private $users;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_enum_expected;

    public function __construct()
    {
        $this->wards = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->application_usual_resid = new ArrayCollection();
        $this->workApplications = new ArrayCollection();
        $this->users = new ArrayCollection();
    }


     /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function initializeCreatedTime()
    {
        if (empty($this->createdTime)) {
            $this->createdTime = new \DateTime();
            $this->isDeleted = False;
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDcode(): ?string
    {
        return $this->dcode;
    }

    public function setDcode(string $dcode): self
    {
        $this->dcode = $dcode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedTime(): ?\DateTimeInterface
    {
        return $this->createdTime;
    }

    public function setCreatedTime(?\DateTimeInterface $createdTime): self
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    public function getModifiedTime(): ?\DateTimeInterface
    {
        return $this->modifiedTime;
    }

    public function setModifiedTime(?\DateTimeInterface $modifiedTime): self
    {
        $this->modifiedTime = $modifiedTime;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
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


    public function jsonSerialize():array
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'dcode' => $this->dcode,
            'fdcode' => $this->fdcode,
            'lga' => $this->lga,
            'percentage' => $this->getPercentageCandidature(),
            'totalLP' => $this->getTotalSelection(),
        );
    }

    public function getFdcode(): ?string
    {
        return $this->fdcode;
    }

    public function setFdcode(string $fdcode): self
    {
        $this->fdcode = $fdcode;

        return $this;
    }

    /**
     * @return Collection<int, Wards>
     */
    public function getWards(): Collection
    {
        return $this->wards;
    }

    public function addWard(Wards $ward): self
    {
        if (!$this->wards->contains($ward)) {
            $this->wards[] = $ward;
            $ward->setDistrict($this);
        }

        return $this;
    }

    public function removeWard(Wards $ward): self
    {
        if ($this->wards->removeElement($ward)) {
            // set the owning side to null (unless already changed)
            if ($ward->getDistrict() === $this) {
                $ward->setDistrict(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Applications>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Applications $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setDistrict($this);
        }

        return $this;
    }

    public function removeApplication(Applications $application): self
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getDistrict() === $this) {
                $application->setDistrict(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Applications>
     */
    public function getApplicationUsualResid(): Collection
    {
        return $this->application_usual_resid;
    }

    public function addApplicationUsualResid(Applications $applicationUsualResid): self
    {
        if (!$this->application_usual_resid->contains($applicationUsualResid)) {
            $this->application_usual_resid[] = $applicationUsualResid;
            $applicationUsualResid->setUsualDistrictResidence($this);
        }

        return $this;
    }

    public function removeApplicationUsualResid(Applications $applicationUsualResid): self
    {
        if ($this->application_usual_resid->removeElement($applicationUsualResid)) {
            // set the owning side to null (unless already changed)
            if ($applicationUsualResid->getUsualDistrictResidence() === $this) {
                $applicationUsualResid->setUsualDistrictResidence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Applications>
     */
    public function getWorkApplications(): Collection
    {
        return $this->workApplications;
    }

    public function addWorkApplication(Applications $workApplication): self
    {
        if (!$this->workApplications->contains($workApplication)) {
            $this->workApplications[] = $workApplication;
            $workApplication->setWorkDistrict($this);
        }

        return $this;
    }

    public function removeWorkApplication(Applications $workApplication): self
    {
        if ($this->workApplications->removeElement($workApplication)) {
            // set the owning side to null (unless already changed)
            if ($workApplication->getWorkDistrict() === $this) {
                $workApplication->setWorkDistrict(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setDistrict($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getDistrict() === $this) {
                $user->setDistrict(null);
            }
        }

        return $this;
    }

    public function getNbEnumExpected(): ?int
    {
        return $this->nb_enum_expected;
    }

    public function setNbEnumExpected(?int $nb_enum_expected): self
    {
        $this->nb_enum_expected = $nb_enum_expected;

        return $this;
    }


    public function getPercentageCandidature(): ?float
    {
        $percent = 0;
        if ($this->nb_enum_expected != 0) {
            $percent = (count($this->applications) * 100) / $this->nb_enum_expected;
        }
        return $percent;
    }


    /**
     * Return selected number
     *
     * @return integer|null
    */
    public function getTotalSelection(): ?int
    {
        return array_reduce($this->applications->toArray(), function ($total, $p) {
            return $total + ((
                $p->getIsSelected() == true &&
                ($p->isOnWaitingList() == false || $p->isOnWaitingList() == null)
            )
                ? 1
                : 0);
        }, 0);
    }

}

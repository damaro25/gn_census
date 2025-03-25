<?php

namespace App\Entity;

use App\Repository\LgasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Table(name="census_lgas")
 * @ORM\Entity(repositoryClass=LgasRepository::class)
 * @UniqueEntity("code", message="A Area already has this code !")
 * @Gedmo\Loggable
 */
class Lgas implements JsonSerializable
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
    private $code;

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
     * @ORM\OneToMany(targetEntity=Districts::class, mappedBy="lga")
     */
    private $districts;

    /**
     * @ORM\OneToMany(targetEntity=Applications::class, mappedBy="lga")
     */
    private $applications;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="lga")
     */
    private $users;

    public function __construct()
    {
        $this->districts = new ArrayCollection();
        $this->applications = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function jsonSerialize():array
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code
        );
    }

    /**
     * @return Collection<int, Districts>
     */
    public function getDistricts(): Collection
    {
        return $this->districts;
    }

    public function addDistrict(Districts $district): self
    {
        if (!$this->districts->contains($district)) {
            $this->districts[] = $district;
            $district->setLga($this);
        }

        return $this;
    }

    public function removeDistrict(Districts $district): self
    {
        if ($this->districts->removeElement($district)) {
            // set the owning side to null (unless already changed)
            if ($district->getLga() === $this) {
                $district->setLga(null);
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
            $application->setLga($this);
        }

        return $this;
    }

    public function removeApplication(Applications $application): self
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getLga() === $this) {
                $application->setLga(null);
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
            $user->setLga($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getLga() === $this) {
                $user->setLga(null);
            }
        }

        return $this;
    }
}

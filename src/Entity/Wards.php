<?php

namespace App\Entity;

use App\Repository\WardsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=WardsRepository::class)
 * @ORM\Table(name="census_wards")
 * @Gedmo\Loggable
 */
class Wards implements JsonSerializable
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
    private $wcode;

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
     * @ORM\Column(type="string", length=45)
     */
    private $fwcode;

    /**
     * @ORM\ManyToOne(targetEntity=Districts::class, inversedBy="wards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $district;


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

    public function getWcode(): ?string
    {
        return $this->wcode;
    }

    public function setWcode(string $wcode): self
    {
        $this->wcode = $wcode;

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

    public function getFwcode(): ?string
    {
        return $this->fwcode;
    }

    public function setFwcode(string $fwcode): self
    {
        $this->fwcode = $fwcode;

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


    public function jsonSerialize():array
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'wcode' => $this->wcode,
            'fwcode' => $this->fwcode,
            'district' => $this->district,
        );
    }

}

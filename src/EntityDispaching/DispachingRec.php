<?php

namespace App\EntityDispaching;

use Doctrine\ORM\Mapping as ORM;

/**
 * DispachingRec
 *
 * @ORM\Table(name="dispaching_rec", indexes={@ORM\Index(name="dispaching_rec_level_1_id", columns={"level_1_id"})})
 * @ORM\Entity
 */
class DispachingRec
{
   /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="dispaching_rec_id", type="integer", nullable=false)
     */
    private $dispachingRecId;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dr_type", type="integer", nullable=true)
     */
    private $drType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dr_latitude", type="float", nullable=true)
     */
    private $drLatitude;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dr_longitude", type="float", nullable=true)
     */
    private $drLongitude;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dr_zs", type="text", nullable=true)
     */
    private $drZs;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dr_zc", type="text", nullable=true)
     */
    private $drZc;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dr_idagent", type="text", nullable=true)
     */
    private $drIdagent;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dr_statut", type="integer", nullable=true)
     */
    private $drStatut;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dr_info", type="text", nullable=true)
     */
    private $drInfo;

    /**
     * @var int|null
     *
     * @ORM\Column(name="get_cons", type="integer", nullable=true)
     */
    private $getCons;

    /**
     * @var int|null
     *
     * @ORM\Column(name="get_menage", type="integer", nullable=true)
     */
    private $getMenage;

    /**
     * @var int|null
     *
     * @ORM\Column(name="get_tpk", type="integer", nullable=true)
     */
    private $getTpk;

    public function getDispachingRecId(): ?int
    {
        return $this->dispachingRecId;
    }

    public function setDispachingRecId(int $dispachingRecId): self
    {
        $this->dispachingRecId = $dispachingRecId;

        return $this;
    }

    public function getLevel1Id(): ?int
    {
        return $this->level1Id;
    }

    public function setLevel1Id(int $level1Id): self
    {
        $this->level1Id = $level1Id;

        return $this;
    }

    public function getDrType(): ?int
    {
        return $this->drType;
    }

    public function setDrType(?int $drType): self
    {
        $this->drType = $drType;

        return $this;
    }

    public function getDrLatitude(): ?float
    {
        return $this->drLatitude;
    }

    public function setDrLatitude(?float $drLatitude): self
    {
        $this->drLatitude = $drLatitude;

        return $this;
    }

    public function getDrLongitude(): ?float
    {
        return $this->drLongitude;
    }

    public function setDrLongitude(?float $drLongitude): self
    {
        $this->drLongitude = $drLongitude;

        return $this;
    }

    public function getDrZs(): ?string
    {
        return $this->drZs;
    }

    public function setDrZs(?string $drZs): self
    {
        $this->drZs = $drZs;

        return $this;
    }

    public function getDrZc(): ?string
    {
        return $this->drZc;
    }

    public function setDrZc(?string $drZc): self
    {
        $this->drZc = $drZc;

        return $this;
    }

    public function getDrIdagent(): ?string
    {
        return $this->drIdagent;
    }

    public function setDrIdagent(?string $drIdagent): self
    {
        $this->drIdagent = $drIdagent;

        return $this;
    }

    public function getDrStatut(): ?int
    {
        return $this->drStatut;
    }

    public function setDrStatut(?int $drStatut): self
    {
        $this->drStatut = $drStatut;

        return $this;
    }

    public function getDrInfo(): ?string
    {
        return $this->drInfo;
    }

    public function setDrInfo(?string $drInfo): self
    {
        $this->drInfo = $drInfo;

        return $this;
    }

    public function getGetCons(): ?int
    {
        return $this->getCons;
    }

    public function setGetCons(?int $getCons): self
    {
        $this->getCons = $getCons;

        return $this;
    }

    public function getGetMenage(): ?int
    {
        return $this->getMenage;
    }

    public function setGetMenage(?int $getMenage): self
    {
        $this->getMenage = $getMenage;

        return $this;
    }

    public function getGetTpk(): ?int
    {
        return $this->getTpk;
    }

    public function setGetTpk(?int $getTpk): self
    {
        $this->getTpk = $getTpk;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


}

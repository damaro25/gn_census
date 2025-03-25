<?php

namespace App\EntityZoneControle;

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
    * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer",name="dispaching_rec_id")
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
     * @ORM\Column(name="zc_latitude", type="float", nullable=true)
     */
    private $zcLatitude;

    /**
     * @var float|null
     *
     * @ORM\Column(name="zc_longitude", type="float", nullable=true)
     */
    private $zcLongitude;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zc_zs", type="text", nullable=true)
     */
    private $zcZs;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zc_zc", type="text", nullable=true)
     */
    private $zcZc;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zc_idagent", type="text", nullable=true)
     */
    private $zcIdagent;

    /**
     * @var int|null
     *
     * @ORM\Column(name="zc_statut", type="integer", nullable=true)
     */
    private $zcStatut;

    /**
     * @var int|null
     *
     * @ORM\Column(name="zc_info", type="integer", nullable=true)
     */
    private $zcInfo;

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

    public function getZcLatitude(): ?float
    {
        return $this->zcLatitude;
    }

    public function setZcLatitude(?float $zcLatitude): self
    {
        $this->zcLatitude = $zcLatitude;

        return $this;
    }

    public function getZcLongitude(): ?float
    {
        return $this->zcLongitude;
    }

    public function setZcLongitude(?float $zcLongitude): self
    {
        $this->zcLongitude = $zcLongitude;

        return $this;
    }

    public function getZcZs(): ?string
    {
        return $this->zcZs;
    }

    public function setZcZs(?string $zcZs): self
    {
        $this->zcZs = $zcZs;

        return $this;
    }

    public function getZcZc(): ?string
    {
        return $this->zcZc;
    }

    public function setZcZc(?string $zcZc): self
    {
        $this->zcZc = $zcZc;

        return $this;
    }

    public function getZcIdagent(): ?string
    {
        return $this->zcIdagent;
    }

    public function setZcIdagent(?string $zcIdagent): self
    {
        $this->zcIdagent = $zcIdagent;

        return $this;
    }

    public function getZcStatut(): ?int
    {
        return $this->zcStatut;
    }

    public function setZcStatut(?int $zcStatut): self
    {
        $this->zcStatut = $zcStatut;

        return $this;
    }

    public function getZcInfo(): ?int
    {
        return $this->zcInfo;
    }

    public function setZcInfo(?int $zcInfo): self
    {
        $this->zcInfo = $zcInfo;

        return $this;
    }


}

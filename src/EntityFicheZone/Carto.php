<?php

namespace App\EntityFicheZone;

use Doctrine\ORM\Mapping as ORM;

/**
 * Carto
 *
 * @ORM\Table(name="carto", indexes={@ORM\Index(name="carto_level_1_id", columns={"level_1_id"})})
 * @ORM\Entity
 */
class Carto
{
    /**
    * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer",name="carto_id")
     */
    private $cartoId;

    /**
     * @var int
     * 
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level_1_id;

/**
     * @ORM\Column(type="integer")
     */
    private $occ;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numero_zone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $zone_troupeau;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $categorie_zone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type_point_eau;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autre_type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $distance_km;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $distance_heure;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $debut_passage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fin_passage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $observation;

    public function getcartoId(): ?int
    {
        return $this->cartoId;
    }

    public function setcartoId(int $cartoId): self
    {
        $this->cartoId = $cartoId;

        return $this;
    }

    public function getLevel1Id(): ?int
    {
        return $this->level_1_id;
    }

    public function setLevel1Id(int $level_1_id): self
    {
        $this->level_1_id = $level_1_id;

        return $this;
    }

    public function getOcc(): ?int
    {
        return $this->occ;
    }

    public function setOcc(int $occ): self
    {
        $this->occ = $occ;

        return $this;
    }

    public function getNumeroZone(): ?int
    {
        return $this->numero_zone;
    }

    public function setNumeroZone(?int $numero_zone): self
    {
        $this->numero_zone = $numero_zone;

        return $this;
    }

    public function getZoneTroupeau(): ?int
    {
        return $this->zone_troupeau;
    }

    public function setZoneTroupeau(?int $zone_troupeau): self
    {
        $this->zone_troupeau = $zone_troupeau;

        return $this;
    }

    public function getCategorieZone(): ?int
    {
        return $this->categorie_zone;
    }

    public function setCategorieZone(?int $categorie_zone): self
    {
        $this->categorie_zone = $categorie_zone;

        return $this;
    }

    public function getTypePointEau(): ?int
    {
        return $this->type_point_eau;
    }

    public function setTypePointEau(?int $type_point_eau): self
    {
        $this->type_point_eau = $type_point_eau;

        return $this;
    }

    public function getAutreType(): ?string
    {
        return $this->autre_type;
    }

    public function setAutreType(?string $autre_type): self
    {
        $this->autre_type = $autre_type;

        return $this;
    }

    public function getDistanceKm(): ?int
    {
        return $this->distance_km;
    }

    public function setDistanceKm(?int $distance_km): self
    {
        $this->distance_km = $distance_km;

        return $this;
    }

    public function getDistanceHeure(): ?int
    {
        return $this->distance_heure;
    }

    public function setDistanceHeure(?int $distance_heure): self
    {
        $this->distance_heure = $distance_heure;

        return $this;
    }

    public function getDebutPassage(): ?int
    {
        return $this->debut_passage;
    }

    public function setDebutPassage(?int $debut_passage): self
    {
        $this->debut_passage = $debut_passage;

        return $this;
    }

    public function getFinPassage(): ?int
    {
        return $this->fin_passage;
    }

    public function setFinPassage(?int $fin_passage): self
    {
        $this->fin_passage = $fin_passage;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }

}

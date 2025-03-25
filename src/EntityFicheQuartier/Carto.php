<?php

namespace App\EntityFicheQuartier;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $statut_localite;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type_de_localite;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type_de_rattachement;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom_chef_quartier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $num_chef_quartier;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom_contact;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $num_contact;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fonct_contact;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $rattachement_geo;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $autre_rattachement_geo;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $rattachement_reel;

    /**
     * @ORM\Column(type="string", nullable=true)
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

    public function getStatutLocalite(): ?int
    {
        return $this->statut_localite;
    }

    public function setStatutLocalite(?int $statut_localite): self
    {
        $this->statut_localite = $statut_localite;

        return $this;
    }

    public function getTypeDeLocalite(): ?int
    {
        return $this->type_de_localite;
    }

    public function setTypeDeLocalite(?int $type_de_localite): self
    {
        $this->type_de_localite = $type_de_localite;

        return $this;
    }

    public function getTypeDeRattachement(): ?int
    {
        return $this->type_de_rattachement;
    }

    public function setTypeDeRattachement(?int $type_de_rattachement): self
    {
        $this->type_de_rattachement = $type_de_rattachement;

        return $this;
    }

    public function getSurnameChefQuartier(): ?string
    {
        return $this->nom_chef_quartier;
    }

    public function setNomChefQuartier(?string $nom_chef_quartier): self
    {
        $this->nom_chef_quartier = $nom_chef_quartier;

        return $this;
    }

    public function getNumChefQuartier(): ?int
    {
        return $this->num_chef_quartier;
    }

    public function setNumChefQuartier(?int $num_chef_quartier): self
    {
        $this->num_chef_quartier = $num_chef_quartier;

        return $this;
    }

    public function getSurnameContact(): ?string
    {
        return $this->nom_contact;
    }

    public function setNomContact(?string $nom_contact): self
    {
        $this->nom_contact = $nom_contact;

        return $this;
    }

    public function getNumContact(): ?int
    {
        return $this->num_contact;
    }

    public function setNumContact(?int $num_contact): self
    {
        $this->num_contact = $num_contact;

        return $this;
    }

    public function getFonctContact(): ?string
    {
        return $this->fonct_contact;
    }

    public function setFonctContact(?string $fonct_contact): self
    {
        $this->fonct_contact = $fonct_contact;

        return $this;
    }

    public function getRattachementGeo(): ?string
    {
        return $this->rattachement_geo;
    }

    public function setRattachementGeo(?string $rattachement_geo): self
    {
        $this->rattachement_geo = $rattachement_geo;

        return $this;
    }

    public function getAutreRattachementGeo(): ?string
    {
        return $this->autre_rattachement_geo;
    }

    public function setAutreRattachementGeo(?string $autre_rattachement_geo): self
    {
        $this->autre_rattachement_geo = $autre_rattachement_geo;

        return $this;
    }

    public function getRattachementReel(): ?string
    {
        return $this->rattachement_reel;
    }

    public function setRattachementReel(?string $rattachement_reel): self
    {
        $this->rattachement_reel = $rattachement_reel;

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

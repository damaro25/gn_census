<?php

namespace App\EntityFicheQuartier;

use Doctrine\ORM\Mapping as ORM;

/**
 * IdentificationRec
 *
 * @ORM\Table(name="identification_rec", indexes={@ORM\Index(name="identification_rec_level_1_id", columns={"level_1_id"})})
 * @ORM\Entity
 */
class IdentificationRec
{

   /**
    * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer",name="identification_rec_id")
     */
    private $identificationRecId;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id; 

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $f_region;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $f_departement;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cva;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $commune;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dr_2012;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dr_2022;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mois;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $annee;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom_agent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $code_agent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom_chef_eq;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $code_chef_eq;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom_sup;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $code_sup;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom_loc;


    public function getidentificationRecId(): ?int
    {
        return $this->identificationRecId;
    }

    public function setidentificationRecId(int $identificationRecId): self
    {
        $this->identificationRecId = $identificationRecId;

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

    public function getFRegion(): ?string
    {
        return $this->f_region;
    }

    public function setFRegion(?string $f_region): self
    {
        $this->f_region = $f_region;

        return $this;
    }

    public function getFDepartement(): ?string
    {
        return $this->f_departement;
    }

    public function setFDepartement(?string $f_departement): self
    {
        $this->f_departement = $f_departement;

        return $this;
    }

    public function getCva(): ?string
    {
        return $this->cva;
    }

    public function setCva(?string $cva): self
    {
        $this->cva = $cva;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getDr2012(): ?string
    {
        return $this->dr_2012;
    }

    public function setDr2012(?string $dr_2012): self
    {
        $this->dr_2012 = $dr_2012;

        return $this;
    }

    public function getDr2022(): ?string
    {
        return $this->dr_2022;
    }

    public function setDr2022(?string $dr_2022): self
    {
        $this->dr_2022 = $dr_2022;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getJour(): ?int
    {
        return $this->jour;
    }

    public function setJour(?int $jour): self
    {
        $this->jour = $jour;

        return $this;
    }

    public function getMois(): ?int
    {
        return $this->mois;
    }

    public function setMois(?int $mois): self
    {
        $this->mois = $mois;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(?int $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getSurnameAgent(): ?string
    {
        return $this->nom_agent;
    }

    public function setNomAgent(?string $nom_agent): self
    {
        $this->nom_agent = $nom_agent;

        return $this;
    }

    public function getCodeAgent(): ?string
    {
        return $this->code_agent;
    }

    public function setCodeAgent(?string $code_agent): self
    {
        $this->code_agent = $code_agent;

        return $this;
    }

    public function getSurnameChefEq(): ?string
    {
        return $this->nom_chef_eq;
    }

    public function setNomChefEq(?string $nom_chef_eq): self
    {
        $this->nom_chef_eq = $nom_chef_eq;

        return $this;
    }

    public function getCodeChefEq(): ?string
    {
        return $this->code_chef_eq;
    }

    public function setCodeChefEq(?string $code_chef_eq): self
    {
        $this->code_chef_eq = $code_chef_eq;

        return $this;
    }

    public function getSurnameSup(): ?string
    {
        return $this->nom_sup;
    }

    public function setNomSup(?string $nom_sup): self
    {
        $this->nom_sup = $nom_sup;

        return $this;
    }

    public function getCodeSup(): ?string
    {
        return $this->code_sup;
    }

    public function setCodeSup(?string $code_sup): self
    {
        $this->code_sup = $code_sup;

        return $this;
    }

    public function getSurnameLoc(): ?string
    {
        return $this->nom_loc;
    }

    public function setNomLoc(?string $nom_loc): self
    {
        $this->nom_loc = $nom_loc;

        return $this;
    }


}

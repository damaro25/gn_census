<?php

namespace App\EntityFicheQuartier;

use Doctrine\ORM\Mapping as ORM;

/**
 * SectionCFicheLogistique
 *
 * @ORM\Table(name="section_c_fiche_logistique", indexes={@ORM\Index(name="section_c_fiche_logistique_level_1_id", columns={"level_1_id"})})
 * @ORM\Entity
 */

class SectionCFicheLogistique
{
    /**
    * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer",name="section_c_fiche_logistique_id")
     */

    private $sectionCFicheLogistiqueId;

     /**
     * @var int
     * 
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $leve1Id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $piste_voies;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autre_piste;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deplacements;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autre_moyens;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $voie_acces;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $difficulte;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dialecte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autre_dialecte;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dialecte_2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autre_dialecte_2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hebergement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type_hebergement;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $marche;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $precision_marche;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jour_marche;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mois_inacces;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pop_flottante;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $renseignement;

    public function getsectionCFicheLogistiqueId(): ?int
    {
        return $this->sectionCFicheLogistiqueId;
    }

    public function setsectionCFicheLogistiqueId(int $sectionCFicheLogistiqueId): self
    {
        $this->sectionCFicheLogistiqueId = $sectionCFicheLogistiqueId;

        return $this;
    }

    public function getLeve1Id(): ?int
    {
        return $this->leve1Id;
    }

    public function setLeve1Id(int $leve1Id): self
    {
        $this->leve1Id = $leve1Id;

        return $this;
    }

    public function getPisteVoies(): ?string
    {
        return $this->piste_voies;
    }

    public function setPisteVoies(?string $piste_voies): self
    {
        $this->piste_voies = $piste_voies;

        return $this;
    }

    public function getAutrePiste(): ?string
    {
        return $this->autre_piste;
    }

    public function setAutrePiste(?string $autre_piste): self
    {
        $this->autre_piste = $autre_piste;

        return $this;
    }

    public function getDeplacements(): ?string
    {
        return $this->deplacements;
    }

    public function setDeplacements(?string $deplacements): self
    {
        $this->deplacements = $deplacements;

        return $this;
    }

    public function getAutreMoyens(): ?string
    {
        return $this->autre_moyens;
    }

    public function setAutreMoyens(?string $autre_moyens): self
    {
        $this->autre_moyens = $autre_moyens;

        return $this;
    }

    public function getVoieAcces(): ?string
    {
        return $this->voie_acces;
    }

    public function setVoieAcces(?string $voie_acces): self
    {
        $this->voie_acces = $voie_acces;

        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(?string $difficulte): self
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    public function getDialecte(): ?int
    {
        return $this->dialecte;
    }

    public function setDialecte(?int $dialecte): self
    {
        $this->dialecte = $dialecte;

        return $this;
    }

    public function getAutreDialecte(): ?string
    {
        return $this->autre_dialecte;
    }

    public function setAutreDialecte(?string $autre_dialecte): self
    {
        $this->autre_dialecte = $autre_dialecte;

        return $this;
    }

    public function getDialecte2(): ?int
    {
        return $this->dialecte_2;
    }

    public function setDialecte2(?int $dialecte_2): self
    {
        $this->dialecte_2 = $dialecte_2;

        return $this;
    }

    public function getAutreDialecte2(): ?string
    {
        return $this->autre_dialecte_2;
    }

    public function setAutreDialecte2(?string $autre_dialecte_2): self
    {
        $this->autre_dialecte_2 = $autre_dialecte_2;

        return $this;
    }

    public function getHebergement(): ?int
    {
        return $this->hebergement;
    }

    public function setHebergement(?int $hebergement): self
    {
        $this->hebergement = $hebergement;

        return $this;
    }

    public function getTypeHebergement(): ?string
    {
        return $this->type_hebergement;
    }

    public function setTypeHebergement(?string $type_hebergement): self
    {
        $this->type_hebergement = $type_hebergement;

        return $this;
    }

    public function getMarche(): ?int
    {
        return $this->marche;
    }

    public function setMarche(?int $marche): self
    {
        $this->marche = $marche;

        return $this;
    }

    public function getPrecisionMarche(): ?string
    {
        return $this->precision_marche;
    }

    public function setPrecisionMarche(?string $precision_marche): self
    {
        $this->precision_marche = $precision_marche;

        return $this;
    }

    public function getJourMarche(): ?int
    {
        return $this->jour_marche;
    }

    public function setJourMarche(?int $jour_marche): self
    {
        $this->jour_marche = $jour_marche;

        return $this;
    }

    public function getMoisInacces(): ?string
    {
        return $this->mois_inacces;
    }

    public function setMoisInacces(?string $mois_inacces): self
    {
        $this->mois_inacces = $mois_inacces;

        return $this;
    }

    public function getPopFlottante(): ?int
    {
        return $this->pop_flottante;
    }

    public function setPopFlottante(?int $pop_flottante): self
    {
        $this->pop_flottante = $pop_flottante;

        return $this;
    }

    public function getRenseignement(): ?string
    {
        return $this->renseignement;
    }

    public function setRenseignement(?string $renseignement): self
    {
        $this->renseignement = $renseignement;

        return $this;
    }
}

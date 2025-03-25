<?php

namespace App\EntityMenage;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenageRec
 *
 * @ORM\Table(name="menage_rec", indexes={@ORM\Index(name="menage_rec_level_1_id", columns={"level_1_id"})})
 * @ORM\Entity
 */
class MenageRec
{
    /**
     * @var int
     *
     * @ORM\Column(name="menage_rec_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $menageRecId;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="men_num_cons", type="text", nullable=true)
     */
    private $menNumCons;

    /**
     * @var string|null
     *
     * @ORM\Column(name="men_cm", type="text", nullable=true)
     */
    private $menCm;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_type", type="integer", nullable=true)
     */
    private $menType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_typecollectif", type="integer", nullable=true)
     */
    private $menTypecollectif;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_sexecm", type="integer", nullable=true)
     */
    private $menSexecm;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_taille", type="integer", nullable=true)
     */
    private $menTaille;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_taille_reel", type="integer", nullable=true)
     */
    private $menTailleReel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_coord", type="integer", nullable=true)
     */
    private $menCoord;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_tel", type="integer", nullable=true)
     */
    private $menTel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_agri", type="integer", nullable=true)
     */
    private $menAgri;

    /**
     * @var string|null
     *
     * @ORM\Column(name="men_autreagri", type="text", nullable=true)
     */
    private $menAutreagri;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_statut", type="integer", nullable=true)
     */
    private $menStatut;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_etat", type="integer", nullable=true)
     */
    private $menEtat;

    /**
     * @var string|null
     *
     * @ORM\Column(name="men_adress", type="text", nullable=true)
     */
    private $menAdress;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_control_date", type="integer", nullable=true)
     */
    private $menControlDate;

      /**
     * @ORM\Column(type="integer", nullable=true ,name="men_validation")
     */
    private $menageValidation;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_composition")
     */
    private $menComposition;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_habitat")
     */
    private $menHabitat;

    /**
     * @ORM\Column(type="integer", nullable=true, name="men_agriculture")
     */
    private $menAgriculture;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_deces")
     */
    private $menDeces;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_emigration")
     */
    private $menEmigration;

    /**
     * @ORM\Column(type="integer", nullable=true , name="nb_deces")
     */
    private $nbDeces;

    /**
     * @ORM\Column(type="integer", nullable=true , name="nb_emigres")
     */
    private $nbEmigres;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_start")
     */
    private $menStart;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_end")
     */
    private $menEnd;

    /**
     * @ORM\Column(type="integer", nullable=true , name="men_info_gen")
     */
    private $menInfoGen;


    public function getMenageRecId(): ?int
    {
        return $this->menageRecId;
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

    public function getMenNumCons(): ?string
    {
        return $this->menNumCons;
    }

    public function setMenNumCons(?string $menNumCons): self
    {
        $this->menNumCons = $menNumCons;

        return $this;
    }

    public function getMenCm(): ?string
    {
        return $this->menCm;
    }

    public function setMenCm(?string $menCm): self
    {
        $this->menCm = $menCm;

        return $this;
    }

    public function getMenType(): ?int
    {
        return $this->menType;
    }

    public function setMenType(?int $menType): self
    {
        $this->menType = $menType;

        return $this;
    }

    public function getMenTypecollectif(): ?int
    {
        return $this->menTypecollectif;
    }

    public function setMenTypecollectif(?int $menTypecollectif): self
    {
        $this->menTypecollectif = $menTypecollectif;

        return $this;
    }

    public function getMenSexecm(): ?int
    {
        return $this->menSexecm;
    }

    public function setMenSexecm(?int $menSexecm): self
    {
        $this->menSexecm = $menSexecm;

        return $this;
    }

    public function getMenTaille(): ?int
    {
        return $this->menTaille;
    }

    public function setMenTaille(?int $menTaille): self
    {
        $this->menTaille = $menTaille;

        return $this;
    }

    public function getMenTailleReel(): ?int
    {
        return $this->menTailleReel;
    }

    public function setMenTailleReel(?int $menTailleReel): self
    {
        $this->menTailleReel = $menTailleReel;

        return $this;
    }

    public function getMenCoord(): ?int
    {
        return $this->menCoord;
    }

    public function setMenCoord(?int $menCoord): self
    {
        $this->menCoord = $menCoord;

        return $this;
    }

    public function getMenTel(): ?int
    {
        return $this->menTel;
    }

    public function setMenTel(?int $menTel): self
    {
        $this->menTel = $menTel;

        return $this;
    }

    public function getMenAgri(): ?int
    {
        return $this->menAgri;
    }

    public function setMenAgri(?int $menAgri): self
    {
        $this->menAgri = $menAgri;

        return $this;
    }

    public function getMenAutreagri(): ?string
    {
        return $this->menAutreagri;
    }

    public function setMenAutreagri(?string $menAutreagri): self
    {
        $this->menAutreagri = $menAutreagri;

        return $this;
    }

    public function getMenStatut(): ?int
    {
        return $this->menStatut;
    }

    public function setMenStatut(?int $menStatut): self
    {
        $this->menStatut = $menStatut;

        return $this;
    }

    public function getMenEtat(): ?int
    {
        return $this->menEtat;
    }

    public function setMenEtat(?int $menEtat): self
    {
        $this->menEtat = $menEtat;

        return $this;
    }

    public function getMenAdress(): ?string
    {
        return $this->menAdress;
    }

    public function setMenAdress(?string $menAdress): self
    {
        $this->menAdress = $menAdress;

        return $this;
    }

    public function getMenControlDate(): ?int
    {
        return $this->menControlDate;
    }

    public function setMenControlDate(?int $menControlDate): self
    {
        $this->menControlDate = $menControlDate;

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenageValidation(): ?int
    {
        return $this->menageValidation;
    }

    public function setMenageValidation(?int $menageValidation): self
    {
        $this->menageValidation = $menageValidation;

        return $this;
    }

    public function getMenComposition(): ?int
    {
        return $this->menComposition;
    }

    public function setMenComposition(?int $menComposition): self
    {
        $this->menComposition = $menComposition;

        return $this;
    }

    public function getMenHabitat(): ?int
    {
        return $this->menHabitat;
    }

    public function setMenHabitat(?int $menHabitat): self
    {
        $this->menHabitat = $menHabitat;

        return $this;
    }

    public function getMenAgriculture(): ?int
    {
        return $this->menAgriculture;
    }

    public function setMenAgriculture(?int $menAgriculture): self
    {
        $this->menAgriculture = $menAgriculture;

        return $this;
    }

    public function getMenDeces(): ?int
    {
        return $this->menDeces;
    }

    public function setMenDeces(?int $menDeces): self
    {
        $this->menDeces = $menDeces;

        return $this;
    }

    public function getMenEmigration(): ?int
    {
        return $this->menEmigration;
    }

    public function setMenEmigration(?int $menEmigration): self
    {
        $this->menEmigration = $menEmigration;

        return $this;
    }

    public function getNbDeces(): ?int
    {
        return $this->nbDeces;
    }

    public function setNbDeces(?int $nbDeces): self
    {
        $this->nbDeces = $nbDeces;

        return $this;
    }

    public function getNbEmigres(): ?int
    {
        return $this->nbEmigres;
    }

    public function setNbEmigres(?int $nbEmigres): self
    {
        $this->nbEmigres = $nbEmigres;

        return $this;
    }

    public function getMenStart(): ?int
    {
        return $this->menStart;
    }

    public function setMenStart(?int $menStart): self
    {
        $this->menStart = $menStart;

        return $this;
    }

    public function getMenEnd(): ?int
    {
        return $this->menEnd;
    }

    public function setMenEnd(?int $menEnd): self
    {
        $this->menEnd = $menEnd;

        return $this;
    }

    public function getMenInfoGen(): ?int
    {
        return $this->menInfoGen;
    }

    public function setMenInfoGen(?int $menInfoGen): self
    {
        $this->menInfoGen = $menInfoGen;

        return $this;
    }
}

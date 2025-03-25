<?php

namespace App\EntityConcession;

use Doctrine\ORM\Mapping as ORM;

/**
 * ext_cons
 *
 * @ORM\Table(name="ext_cons")
 * @ORM\Entity
 */
class ExtCons
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer",  name="`ext_cons-id`")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="`level-1-id`", type="integer", nullable=false)
     */
    private $level1id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_num")
     */
    private $consNum;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_iden")
     */
    private $consIden;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_statut_real")
     */
    private $consStatutReal;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_statut")
     */
    private $consStatut;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_construction")
     */
    private $consConstruction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_adress")
     */
    private $consAdress;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_typehab")
     */
    private $consTypHab;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="autre_habitat")
     */
    private $autreHabibat;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_nbhab")
     */
    private $consNbHab;

    /**
     * @ORM\Column(type="float", nullable=true, name="cons_lat")
     */
    private $consLat;

    /**
     * @ORM\Column(type="float", nullable=true, name="cons_lon")
     */
    private $consLon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_typeinfras")
     */
    private $consTypeinfras;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_tel")
     */
    private $consTel;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_codeqvh")
     */
    private $consQodqvh;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_qvh")
     */
    private $consQvh;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_agentappui")
     */
    private $consAgentappui;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_obs")
     */
    private $consObs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_collecte")
     */
    private $consCollecte;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true, name="cons_etat")
     */
    private $consEtat;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_control_date")
     */
    private $consControlDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_old_num")
     */
    private $consOldNum;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_men_ord_wait")
     */
    private $consMenOrdWait;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_men_col_wait")
     */
    private $consMenColWait;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_pop_ord_wait")
     */
    private $consPopOrdWait;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_pop_col_wait")
     */
    private $consPopColWait;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_men_ord_reel")
     */
    private $consMenOrdReel;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_men_col_reel")
     */
    private $consMenColReel;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_pop_ord_reel")
     */
    private $consPopOrdReel;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_pop_col_reel")
     */
    private $consPopColReel;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_distance")
     */
    private $consDistance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="cons_concerne")
     */
    private $consConcerne;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_dis_cr")
     */
    private $consDisCr;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_concret_date")
     */
    private $consConcretDate;

    /**
     * @ORM\Column(type="integer", nullable=true, name="cons_infra_habite")
     */
    private $consInfraHabite;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel1id(): ?int
    {
        return $this->level1id;
    }

    public function setLevel1id(int $level1id): self
    {
        $this->level1id = $level1id;

        return $this;
    }

    public function getConsNum(): ?string
    {
        return $this->consNum;
    }

    public function setConsNum(?string $consNum): self
    {
        $this->consNum = $consNum;

        return $this;
    }

    public function getConsIden(): ?string
    {
        return $this->consIden;
    }

    public function setConsIden(?string $consIden): self
    {
        $this->consIden = $consIden;

        return $this;
    }

    public function getConsStatutReal(): ?int
    {
        return $this->consStatutReal;
    }

    public function setConsStatutReal(?int $consStatutReal): self
    {
        $this->consStatutReal = $consStatutReal;

        return $this;
    }

    public function getConsStatut(): ?int
    {
        return $this->consStatut;
    }

    public function setConsStatut(?int $consStatut): self
    {
        $this->consStatut = $consStatut;

        return $this;
    }

    public function getConsConstruction(): ?int
    {
        return $this->consConstruction;
    }

    public function setConsConstruction(?int $consConstruction): self
    {
        $this->consConstruction = $consConstruction;

        return $this;
    }

    public function getConsAdress(): ?string
    {
        return $this->consAdress;
    }

    public function setConsAdress(?string $consAdress): self
    {
        $this->consAdress = $consAdress;

        return $this;
    }

    public function getConsTypHab(): ?int
    {
        return $this->consTypHab;
    }

    public function setConsTypHab(?int $consTypHab): self
    {
        $this->consTypHab = $consTypHab;

        return $this;
    }

    
    public function getAutreHabitat(): ?string
    {
        return $this->autreHabibat;
    }

    public function setAutreHabitat(?string $autreHabibat): self
    {
        $this->autreHabibat = $autreHabibat;

        return $this;
    }

    public function getConsNbHab(): ?int
    {
        return $this->consNbHab;
    }

    public function setConsNbHab(?int $consNbHab): self
    {
        $this->consNbHab = $consNbHab;

        return $this;
    }

    public function getConsLat(): ?float
    {
        return $this->consLat;
    }

    public function setConsLat(?float $consLat): self
    {
        $this->consLat = $consLat;

        return $this;
    }

    public function getConsLon(): ?float
    {
        return $this->consLon;
    }

    public function setConsLon(?float $consLon): self
    {
        $this->consLon = $consLon;

        return $this;
    }

    public function getConsTypeinfras(): ?string
    {
        return $this->consTypeinfras;
    }

    public function setConsTypeinfras(?string $consTypeinfras): self
    {
        $this->consTypeinfras = $consTypeinfras;

        return $this;
    }

    public function getConsTel(): ?int
    {
        return $this->consTel;
    }

    public function setConsTel(?int $consTel): self
    {
        $this->consTel = $consTel;

        return $this;
    }

    public function getConsQodqvh(): ?int
    {
        return $this->consQodqvh;
    }

    public function setConsQodqvh(?int $consQodqvh): self
    {
        $this->consQodqvh = $consQodqvh;

        return $this;
    }

    public function getConsQvh(): ?string
    {
        return $this->consQvh;
    }

    public function setConsQvh(?string $consQvh): self
    {
        $this->consQvh = $consQvh;

        return $this;
    }

    public function getConsAgentappui(): ?string
    {
        return $this->consAgentappui;
    }

    public function setConsAgentappui(?string $consAgentappui): self
    {
        $this->consAgentappui = $consAgentappui;

        return $this;
    }

    public function getConsObs(): ?string
    {
        return $this->consObs;
    }

    public function setConsObs(?string $consObs): self
    {
        $this->consObs = $consObs;

        return $this;
    }

    public function getConsCollecte(): ?string
    {
        return $this->consCollecte;
    }

    public function setConsCollecte(?string $consCollecte): self
    {
        $this->consCollecte = $consCollecte;

        return $this;
    }

    public function getConsEtat(): ?int
    {
        return $this->consEtat;
    }

    public function setConsEtat(?int $consEtat): self
    {
        $this->consEtat = $consEtat;

        return $this;
    }

    public function getConsControlDate(): ?int
    {
        return $this->consControlDate;
    }

    public function setConsControlDate(?int $consControlDate): self
    {
        $this->consControlDate = $consControlDate;

        return $this;
    }

    public function getConsOldNum(): ?string
    {
        return $this->consOldNum;
    }

    public function setConsOldNum(?string $consOldNum): self
    {
        $this->consOldNum = $consOldNum;

        return $this;
    }

    public function getConsMenOrdWait(): ?int
    {
        return $this->consMenOrdWait;
    }

    public function setConsMenOrdWait(?int $consMenOrdWait): self
    {
        $this->consMenOrdWait = $consMenOrdWait;

        return $this;
    }

    public function getConsMenColWait(): ?int
    {
        return $this->consMenColWait;
    }

    public function setConsMenColWait(?int $consMenColWait): self
    {
        $this->consMenColWait = $consMenColWait;

        return $this;
    }

    public function getConsPopColWait(): ?int
    {
        return $this->consPopColWait;
    }

    public function setConsPopColWait(?int $consPopColWait): self
    {
        $this->consPopColWait = $consPopColWait;

        return $this;
    }

    public function getConsPopOrdWait(): ?int
    {
        return $this->consPopOrdWait;
    }

    public function setConsPopOrdWait(?int $consPopOrdWait): self
    {
        $this->consPopOrdWait = $consPopOrdWait;

        return $this;
    }

    public function getConsMenOrdReel(): ?int
    {
        return $this->consMenOrdReel;
    }

    public function setConsMenOrdReel(?int $consMenOrdReel): self
    {
        $this->consMenOrdReel = $consMenOrdReel;

        return $this;
    }

    public function getConsMenColReel(): ?int
    {
        return $this->consMenColReel;
    }

    public function setConsMenColReel(?int $consMenColReel): self
    {
        $this->consMenColReel = $consMenColReel;

        return $this;
    }

    public function getConsPopColReel(): ?int
    {
        return $this->consPopColReel;
    }

    public function setConsPopColReel(?int $consPopColReel): self
    {
        $this->consPopColReel = $consPopColReel;

        return $this;
    }

    public function getConsPopOrdReel(): ?int
    {
        return $this->consPopOrdReel;
    }

    public function setConsPopOrdReel(?int $consPopOrdReel): self
    {
        $this->consPopOrdReel = $consPopOrdReel;

        return $this;
    }

    public function getConsDistance(): ?int
    {
        return $this->consDistance;
    }

    public function setConsDistance(?int $consDistance): self
    {
        $this->consDistance= $consDistance;

        return $this;
    }

    public function getConcerne(): ?string
    {
        return $this->consConcerne;
    }

    public function setConsConcerne(?string $consConcerne): self
    {
        $this->consObs = $consConcerne;

        return $this;
    }

    public function getConsDisCr(): ?int
    {
        return $this->consDisCr;
    }

    public function setConsDisCr(?int $consDisCr): self
    {
        $this->consDisCr= $consDisCr;

        return $this;
    }

    public function getConsConcretDate(): ?int
    {
        return $this->consConcretDate;
    }

    public function setConsConcretDate(?int $consConcretDate): self
    {
        $this->consConcretDate= $consConcretDate;

        return $this;
    }

    public function getConsInfraHabite(): ?int
    {
        return $this->consInfraHabite;
    }

    public function setConsInfraHabite(?int $consInfraHabite): self
    {
        $this->consInfraHabite= $consInfraHabite;

        return $this;
    }

}

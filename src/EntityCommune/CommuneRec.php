<?php

namespace App\EntityCommune;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenageRec
 *
 * @ORM\Table(name="commune_rec", indexes={@ORM\Index(name="commune_rec_level_1_id", columns={"level_1_id"})})
 * @ORM\Entity
 */
class CommuneRec
{
    /**
     * @var int
     *
     * @ORM\Column(name="commune_rec_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $communeRecId;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nom_de_la_commune", type="text", nullable=true)
     */
    private $nomDeLaCommune;

    /**
     * @var int|null
     *
     * @ORM\Column(name="statut_de_la_commune", type="integer", nullable=true)
     */
    private $statutDeLaCommune;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_ce", type="text", nullable=true)
     */
    private $idCe;

    /**
     * @var int|null
     *
     * @ORM\Column(name="id_sync", type="integer", nullable=true)
     */
    private $idSync;


    public function getMenageRecId(): ?int
    {
        return $this->communeRecId;
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

    public function getSurnameDeLaCommune(): ?string
    {
        return $this->nomDeLaCommune;
    }

    public function setNomDeLaCommune(?string $nomDeLaCommune): self
    {
        $this->nomDeLaCommune = $nomDeLaCommune;

        return $this;
    }

    public function getStatutDeLaCommune(): ?int
    {
        return $this->statutDeLaCommune;
    }

    public function setStatutDeLaCommune(?int $statutDeLaCommune): self
    {
        $this->statutDeLaCommune = $statutDeLaCommune;

        return $this;
    }

    public function getIdCe(): ?string
    {
        return $this->idCe;
    }

    public function setIdCe(?string $idCe): self
    {
        $this->idCe = $idCe;

        return $this;
    }

    public function getIdSync(): ?int
    {
        return $this->idSync;
    }

    public function setIdSync(?int $idSync): self
    {
        $this->idSync = $idSync;

        return $this;
    }


}

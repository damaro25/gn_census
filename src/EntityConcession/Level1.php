<?php

namespace App\EntityConcession;

use Doctrine\ORM\Mapping as ORM;

/**
 * Level1
 * 
 * @ORM\Table(name="`level-1`")
 * @ORM\Entity
 */
class Level1
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="`level-1-id`")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="`case-id`")
     */
    private $caseId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="`cons_iddr`")
     */
    private $consIddr;

    /**
     * @ORM\Column(type="string", length=255, name="cons_id_edif")
     */
    private $consIdEdif;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaseId(): ?string
    {
        return $this->caseId;
    }

    public function setCaseId(string $caseId): self
    {
        $this->caseId = $caseId;

        return $this;
    }

    public function getConsIddr(): ?string
    {
        return $this->consIddr;
    }

    public function setConsIddr(string $consIddr): self
    {
        $this->consIddr = $consIddr;

        return $this;
    }

    public function getConsIdEdif(): ?string
    {
        return $this->consIdEdif;
    }

    public function setConsIdEdif(string $consIdEdif): self
    {
        $this->consIdEdif = $consIdEdif;

        return $this;
    }
}

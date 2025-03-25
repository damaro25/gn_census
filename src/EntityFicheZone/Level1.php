<?php

namespace App\EntityFicheZone;

use Doctrine\ORM\Mapping as ORM;

/**
 * Level1
 *
 * @ORM\Table(name="level_1", uniqueConstraints={@ORM\UniqueConstraint(name="level_1_case_id", columns={"case_id"})})
 * @ORM\Entity
 */
class Level1
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer" , name="level_1_id")
     */
    private $level1Id;

    /**
     * @var string
     *
     * @ORM\Column(name="case_id", type="text", nullable=false)
     */
    private $caseId;

   /**
     * @var string|null
     *
     * @ORM\Column(name="login_agent", type="text", nullable=true)
     */
    private $loginAgent;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_chef_equipe", type="text", nullable=true)
     */
    private $idChefEquipe;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code_localite2", type="text", nullable=true)
     */
    private $codeLocalite;

    public function getLevel1Id(): ?int
    {
        return $this->level1Id;
    }

    public function setLevel1Id(int $level1Id): self
    {
        $this->level1Id = $level1Id;

        return $this;
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

   
    public function getLoginAgent(): ?string
    {
        return $this->loginAgent;
    }

    public function setLoginAgent(?string $loginAgent): self
    {
        $this->loginAgent = $loginAgent;

        return $this;
    }

    public function getCodeLocalite(): ?string
    {
        return $this->codeLocalite;
    }

    public function setCodeLocalite(?string $codeLocalite): self
    {
        $this->codeLocalite = $codeLocalite;

        return $this;
    }

    public function getIdChefEquipe(): ?string
    {
        return $this->idChefEquipe;
    }

    public function setIdChefEquipe(?string $idChefEquipe): self
    {
        $this->idChefEquipe = $idChefEquipe;

        return $this;
    }

}

<?php

namespace App\EntityCommune;

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
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $level1Id;

    /**
     * @var string
     *
     * @ORM\Column(name="case_id", type="text", nullable=false)
     */
    private $caseId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="commune_id", type="text", nullable=true)
     */
    private $communeId;

    public function getLevel1Id(): ?int
    {
        return $this->level1Id;
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

    public function getCommuneId(): ?string
    {
        return $this->communeId;
    }

    public function setCommuneId(?string $communeId): self
    {
        $this->communeId = $communeId;

        return $this;
    }


}

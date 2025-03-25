<?php

namespace App\EntityExtAtic;

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
     * @ORM\Column(name="ext_login_atic_id", type="text", nullable=false)
     */
    private $extLoginAtic;


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


    public function getExtLoginAtic(): string
    {
        return $this->extLoginAtic;
    }

    public function setExtLoginAtic(string $extLoginAtic): self
    {
        $this->extLoginAtic = $extLoginAtic;

        return $this;
    }

}

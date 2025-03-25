<?php

namespace App\EntityDispaching;

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
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
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
     * @ORM\Column(name="dr_id", type="text", nullable=true)
     */
    private $drId;

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

    public function getDrId(): ?string
    {
        return $this->drId;
    }

    public function setDrId(?string $drId): self
    {
        $this->drId = $drId;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


}

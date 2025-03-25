<?php

namespace App\EntityMenage;

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
     * @var string|null
     *
     * @ORM\Column(name="men_iddr", type="text", nullable=true)
     */
    private $menIddr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="men_id_edif", type="text", nullable=true)
     */
    private $menIdEdif;

    /**
     * @var int|null
     *
     * @ORM\Column(name="men_num", type="integer", nullable=true)
     */
    private $menNum;

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

    public function getMenIddr(): ?string
    {
        return $this->menIddr;
    }

    public function setMenIddr(?string $menIddr): self
    {
        $this->menIddr = $menIddr;

        return $this;
    }

    public function getMenIdEdif(): ?string
    {
        return $this->menIdEdif;
    }

    public function setMenIdEdif(?string $menIdEdif): self
    {
        $this->menIdEdif = $menIdEdif;

        return $this;
    }

    public function getMenNum(): ?int
    {
        return $this->menNum;
    }

    public function setMenNum(?int $menNum): self
    {
        $this->menNum = $menNum;

        return $this;
    }


}

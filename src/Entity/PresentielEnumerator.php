<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PresentielEnumeratorRepository;

/**
 * @ORM\Entity(repositoryClass=PresentielEnumeratorRepository::class)
 * @ORM\Table(name="census_presentiel_enumerator", uniqueConstraints={@ORM\UniqueConstraint(name="prtiel_uniq_idx", columns={"presentiel_id", "enumerator_classroom_id"})})
 */
class PresentielEnumerator implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPresent;

    /**
     * @ORM\ManyToOne(targetEntity=Presentiel::class, inversedBy="presentielEnumerators")
     * @ORM\JoinColumn(nullable=false)
     */
    private $presentiel;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $opsaisi;

    /**
     * @ORM\ManyToOne(targetEntity=Classroom::class, inversedBy="presentielEnumerators")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enumeratorClassroom;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsPresent(): ?bool
    {
        return $this->isPresent;
    }

    public function setIsPresent(bool $isPresent): self
    {
        $this->isPresent = $isPresent;

        return $this;
    }

    public function getPresentiel(): ?Presentiel
    {
        return $this->presentiel;
    }

    public function setPresentiel(?Presentiel $presentiel): self
    {
        $this->presentiel = $presentiel;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getOpsaisi(): ?User
    {
        return $this->opsaisi;
    }

    public function setOpsaisi(?User $opsaisi): self
    {
        $this->opsaisi = $opsaisi;

        return $this;
    }

    public function getEnumeratorClassroom(): ?Classroom
    {
        return $this->enumeratorClassroom;
    }

    public function setEnumeratorClassroom(?Classroom $enumeratorClassroom): self
    {
        $this->enumeratorClassroom = $enumeratorClassroom;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'enumeratorClassroom' => $this->enumeratorClassroom,
            'enumerator' => $this->enumeratorClassroom->getEnumerator(),
            'isPresent' => $this->isPresent,
        );
    }
}

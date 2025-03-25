<?php

namespace App\Entity;

use App\Repository\PvRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PvRepository::class)
 * @ORM\Table(name="census_pv")
 */
class Pv
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $pv;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pvs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $commission;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPv(): ?string
    {
        return $this->pv;
    }

    public function setPv(string $pv): self
    {
        $this->pv = $pv;

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

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getCommission(): ?User
    {
        return $this->commission;
    }

    public function setCommission(?User $commission): self
    {
        $this->commission = $commission;

        return $this;
    }
}

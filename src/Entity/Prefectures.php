<?php

namespace App\Entity;

use JsonSerializable;
use App\Repository\PrefecturesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrefecturesRepository::class)
 * @ORM\Table(name="census_prefectures")
 */
class Prefectures implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $codeParent;

    /**
     * @ORM\ManyToOne(targetEntity=Regions::class, inversedBy="prefectures")
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity=Communes::class, mappedBy="prefecture")
     */
    private $communes;



    public function jsonSerialize():array
    {
        return array(
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'region' => $this->region,
            'codeParent' => $this->codeParent,
        );
    }

    public function __toString()
    {
        return $this->nom;
    }

    public function __construct()
    {
        $this->communes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodeParent(): ?string
    {
        return $this->codeParent;
    }

    public function setCodeParent(?string $codeParent): self
    {
        $this->codeParent = $codeParent;

        return $this;
    }

    public function getRegion(): ?Regions
    {
        return $this->region;
    }

    public function setRegion(?Regions $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<int, Communes>
     */
    public function getCommunes(): Collection
    {
        return $this->communes;
    }

    public function addCommune(Communes $commune): self
    {
        if (!$this->communes->contains($commune)) {
            $this->communes[] = $commune;
            $commune->setPrefecture($this);
        }

        return $this;
    }

    public function removeCommune(Communes $commune): self
    {
        if ($this->communes->removeElement($commune)) {
            // set the owning side to null (unless already changed)
            if ($commune->getPrefecture() === $this) {
                $commune->setPrefecture(null);
            }
        }

        return $this;
    }
}

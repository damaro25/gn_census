<?php

namespace App\Entity;

use App\Repository\RegionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegionsRepository::class)
 * @ORM\Table(name="census_regions")
 */
class Regions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Prefectures::class, mappedBy="region")
     */
    private $prefectures;

    public function __construct()
    {
        $this->prefectures = new ArrayCollection();
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

    /**
     * @return Collection<int, Prefectures>
     */
    public function getPrefectures(): Collection
    {
        return $this->prefectures;
    }

    public function addPrefecture(Prefectures $prefecture): self
    {
        if (!$this->prefectures->contains($prefecture)) {
            $this->prefectures[] = $prefecture;
            $prefecture->setRegion($this);
        }

        return $this;
    }

    public function removePrefecture(Prefectures $prefecture): self
    {
        if ($this->prefectures->removeElement($prefecture)) {
            // set the owning side to null (unless already changed)
            if ($prefecture->getRegion() === $this) {
                $prefecture->setRegion(null);
            }
        }

        return $this;
    }
}

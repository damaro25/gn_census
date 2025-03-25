<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\PresentielRepository;

use Doctrine\Common\Collections\Collection;
use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=PresentielRepository::class)
 * @ORM\Table(name="census_presentiel", uniqueConstraints={@ORM\UniqueConstraint(name="prens_day_uniq_idx", columns={"supervisor_id", "day_at"})})
 */
class Presentiel implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="presentiels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supervisor;

    /**
     * @ORM\Column(type="date")
     */
    private $dayAt;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $uuid;

    /**
     * @Gedmo\Slug(
     *  fields={"uuid", "createAt"}, 
     *  separator="_",
     *  updatable= false,
     *  unique= false,
     *  dateFormat= "d/m/Y H-i-s",
     * )
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $slug;

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
     * @ORM\OneToMany(targetEntity=PresentielEnumerator::class, mappedBy="presentiel")
     */
    private $presentielEnumerators;

    /**
     * @ORM\OneToMany(targetEntity=PresentielFiles::class, mappedBy="presentiel")
     */
    private $presentielFiles;

    public function __construct()
    {
        $this->presentielEnumerators = new ArrayCollection();
        $this->presentielFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(?User $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    public function getDayAt(): ?\DateTimeInterface
    {
        return $this->dayAt;
    }

    public function setDayAt(\DateTimeInterface $dayAt): self
    {
        $this->dayAt = $dayAt;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    /**
     * @return Collection<int, PresentielEnumerator>
     */
    public function getPresentielEnumerators(): Collection
    {
        return $this->presentielEnumerators;
    }

    public function addPresentielEnumerator(PresentielEnumerator $presentielEnumerator): self
    {
        if (!$this->presentielEnumerators->contains($presentielEnumerator)) {
            $this->presentielEnumerators[] = $presentielEnumerator;
            $presentielEnumerator->setPresentiel($this);
        }

        return $this;
    }

    public function removePresentielEnumerator(PresentielEnumerator $presentielEnumerator): self
    {
        if ($this->presentielEnumerators->removeElement($presentielEnumerator)) {
            // set the owning side to null (unless already changed)
            if ($presentielEnumerator->getPresentiel() === $this) {
                $presentielEnumerator->setPresentiel(null);
            }
        }

        return $this;
    }

    /**
     * Retourne le total des présences
     * 
     * @return int|null
     */
    public function getTotalPresence(): ?int
    {
        return array_reduce($this->presentielEnumerators->toArray(), function ($total, $pr) {
            return $total + ($pr->isIsPresent() ? 1 : 0);
        }, 0);
    }

    /**
     * Retourne le total des absences
     * 
     * @return int|null
     */
    public function getTotalAbsence(): ?int
    {
        return array_reduce($this->presentielEnumerators->toArray(), function ($total, $pr) {
            return $total + (!$pr->isIsPresent() ? 1 : 0);
        }, 0);
    }

    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'dayAt' => $this->dayAt->format('d-m-Y'),
            'slug' => $this->slug,
            'nbrPresence' => $this->getTotalPresence(),
            'nbrAbsence' => $this->getTotalAbsence(),
            'nbrFeuille' => count($this->presentielFiles)
        );
    }

    /**
     * @return Collection<int, PresentielFiles>
     */
    public function getPresentielFiles(): Collection
    {
        return $this->presentielFiles;
    }

    public function addPresentielFile(PresentielFiles $presentielFile): self
    {
        if (!$this->presentielFiles->contains($presentielFile)) {
            $this->presentielFiles[] = $presentielFile;
            $presentielFile->setPresentiel($this);
        }

        return $this;
    }

    public function removePresentielFile(PresentielFiles $presentielFile): self
    {
        if ($this->presentielFiles->removeElement($presentielFile)) {
            // set the owning side to null (unless already changed)
            if ($presentielFile->getPresentiel() === $this) {
                $presentielFile->setPresentiel(null);
            }
        }

        return $this;
    }
}

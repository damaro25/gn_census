<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;


use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\ClassroomRepository;
use Gedmo\Sluggable\Handler\TreeSlugHandler;

/**
 * @ORM\Table(name="census_teams")
 * @ORM\Entity(repositoryClass=ClassroomRepository::class)
 */
class Classroom implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="classrooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supervisor;

    /**
     * @ORM\ManyToOne(targetEntity=Applications::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $enumerator;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=8, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="integer")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isProfile;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleteAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $opsaisi;

    /**
     * @ORM\OneToMany(targetEntity=PresentielEnumerator::class, mappedBy="enumeratorClassroom")
     */
    private $presentielEnumerators;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $deleted;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cswebResponse;

    public function __construct()
    {
        $this->presentielEnumerators = new ArrayCollection();
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

    public function getEnumerator(): ?Applications
    {
        return $this->enumerator;
    }

    public function setEnumerator(?Applications $enumerator): self
    {
        $this->enumerator = $enumerator;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?int
    {
        return $this->password;
    }

    public function setPassword(int $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isIsProfile(): ?bool
    {
        return $this->isProfile;
    }

    public function setIsProfile(?bool $isProfile): self
    {
        $this->isProfile = $isProfile;

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

    public function getDeleteAt(): ?\DateTimeInterface
    {
        return $this->deleteAt;
    }

    public function setDeleteAt(?\DateTimeInterface $deleteAt): self
    {
        $this->deleteAt = $deleteAt;

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
            $presentielEnumerator->setEnumeratorClassroom($this);
        }

        return $this;
    }

    public function removePresentielEnumerator(PresentielEnumerator $presentielEnumerator): self
    {
        if ($this->presentielEnumerators->removeElement($presentielEnumerator)) {
            // set the owning side to null (unless already changed)
            if ($presentielEnumerator->getEnumeratorClassroom() === $this) {
                $presentielEnumerator->setEnumeratorClassroom(null);
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

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(?int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getCswebResponse(): ?int
    {
        return $this->cswebResponse;
    }

    public function setCswebResponse(?int $cswebResponse): self
    {
        $this->cswebResponse = $cswebResponse;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'slug' => $this->slug,
            'enumerator' => $this->enumerator,
            'lga' => $this->enumerator->getLga(),
            'district' => $this->enumerator->getDistrict(),
            'username' => $this->username,
            'password' => $this->password,
            'isProfile' => $this->isProfile,
            'totalPresence' => $this->getTotalPresence(),
            'note' => $this->note == NULL ? 0 : $this->note,
            'cswebResponse' => $this->cswebResponse,
            'deleted' => $this->deleted,
            'supervisor' => $this->supervisor->getSlug(),
        );
    }
}

<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PresentielFilesRepository;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;

/**
 * @ORM\Table(name="census_presentiel_files")
 * @ORM\Entity(repositoryClass=PresentielFilesRepository::class)
 */
class PresentielFiles implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Presentiel::class, inversedBy="presentielFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $presentiel;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $fileName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateAt;

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
     * @ORM\Column(type="integer")
     */
    private $opsaisi;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

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

    public function getOpsaisi(): ?int
    {
        return $this->opsaisi;
    }

    public function setOpsaisi(int $opsaisi): self
    {
        $this->opsaisi = $opsaisi;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'fileName' => $this->fileName,
            'slug' => $this->slug,
        );
    }
}

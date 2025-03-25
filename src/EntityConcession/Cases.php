<?php

namespace App\EntityConcession;

use Doctrine\ORM\Mapping as ORM;
use App\EntityConcession\FileRevisions;

/**
 * Cases
 *
 * @ORM\Table(name="cases")
 * @ORM\Entity
 */
class Cases
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, name="`key`")
     */
    private $key2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $questionnaire;

     /**
     * @var int
     *
     * @ORM\Column(name="last_modified_revision", type="integer", nullable=false)
     */
    private $lastModifiedRevision;

    /**
     * @ORM\Column(type="integer")
     */
    private $deleted;

    /**
     * @ORM\Column(type="integer")
     */
    private $fileOrder;

    /**
     * @ORM\Column(type="integer")
     */
    private $verified;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $partialSaveMode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $partialSaveFieldName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $partialSaveLevelKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $partialSaveRecordOccurrence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $partialSaveItemOccurrence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $partialSaveSubitemOccurrence;


    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $uuid): self
    {
        $this->id = $uuid;

        return $this;
    }


    public function getKey2(): ?string
    {
        return $this->key2;
    }

    public function setKey2(string $key2): self
    {
        $this->key2 = $key2;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getQuestionnaire(): ?string
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(?string $questionnaire): self
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    public function getLastModifiedRevision(): ?int
    {
        return $this->lastModifiedRevision;
    }

    public function setLastModifiedRevision(int $lastModifiedRevision): self
    {
        $this->lastModifiedRevision = $lastModifiedRevision;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getFileOrder(): ?int
    {
        return $this->fileOrder;
    }

    public function setFileOrder(int $fileOrder): self
    {
        $this->fileOrder = $fileOrder;

        return $this;
    }

    public function getVerified(): ?int
    {
        return $this->verified;
    }

    public function setVerified(int $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function getPartialSaveMode(): ?string
    {
        return $this->partialSaveMode;
    }

    public function setPartialSaveMode(?string $partialSaveMode): self
    {
        $this->partialSaveMode = $partialSaveMode;

        return $this;
    }

    public function getPartialSaveFieldName(): ?string
    {
        return $this->partialSaveFieldName;
    }

    public function setPartialSaveFieldName(?string $partialSaveFieldName): self
    {
        $this->partialSaveFieldName = $partialSaveFieldName;

        return $this;
    }

    public function getPartialSaveLevelKey(): ?string
    {
        return $this->partialSaveLevelKey;
    }

    public function setPartialSaveLevelKey(?string $partialSaveLevelKey): self
    {
        $this->partialSaveLevelKey = $partialSaveLevelKey;

        return $this;
    }

    public function getPartialSaveRecordOccurrence(): ?string
    {
        return $this->partialSaveRecordOccurrence;
    }

    public function setPartialSaveRecordOccurrence(?string $partialSaveRecordOccurrence): self
    {
        $this->partialSaveRecordOccurrence = $partialSaveRecordOccurrence;

        return $this;
    }

    public function getPartialSaveItemOccurrence(): ?string
    {
        return $this->partialSaveItemOccurrence;
    }

    public function setPartialSaveItemOccurrence(?string $partialSaveItemOccurrence): self
    {
        $this->partialSaveItemOccurrence = $partialSaveItemOccurrence;

        return $this;
    }

    public function getPartialSaveSubitemOccurrence(): ?string
    {
        return $this->partialSaveSubitemOccurrence;
    }

    public function setPartialSaveSubitemOccurrence(?string $partialSaveSubitemOccurrence): self
    {
        $this->partialSaveSubitemOccurrence = $partialSaveSubitemOccurrence;

        return $this;
    }
}

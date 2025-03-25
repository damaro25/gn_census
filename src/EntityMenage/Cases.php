<?php

namespace App\EntityMenage;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cases
 *
 * @ORM\Table(name="cases")
 * @ORM\Entity
 */
class Cases
{
    /**
     * @var int
     *
     * @ORM\Column(name="idd", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idd;

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="text", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="text", nullable=false)
     */
    private $key;

    /**
     * @var string|null
     *
     * @ORM\Column(name="label", type="text", nullable=true)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="questionnaire", type="text", nullable=false)
     */
    private $questionnaire;

    /**
     * @var int
     *
     * @ORM\Column(name="last_modified_revision", type="integer", nullable=false)
     */
    private $lastModifiedRevision;

    /**
     * @var int
     *
     * @ORM\Column(name="deleted", type="integer", nullable=false)
     */
    private $deleted = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="file_order", type="float", precision=10, scale=0, nullable=false)
     */
    private $fileOrder;

    /**
     * @var int
     *
     * @ORM\Column(name="verified", type="integer", nullable=false)
     */
    private $verified = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="partial_save_mode", type="text", nullable=true)
     */
    private $partialSaveMode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="partial_save_field_name", type="text", nullable=true)
     */
    private $partialSaveFieldName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="partial_save_level_key", type="text", nullable=true)
     */
    private $partialSaveLevelKey;

    /**
     * @var int|null
     *
     * @ORM\Column(name="partial_save_record_occurrence", type="integer", nullable=true)
     */
    private $partialSaveRecordOccurrence;

    /**
     * @var int|null
     *
     * @ORM\Column(name="partial_save_item_occurrence", type="integer", nullable=true)
     */
    private $partialSaveItemOccurrence;

    /**
     * @var int|null
     *
     * @ORM\Column(name="partial_save_subitem_occurrence", type="integer", nullable=true)
     */
    private $partialSaveSubitemOccurrence;

    public function getIdd(): ?int
    {
        return $this->idd;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getQuestionnaire(): ?string
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(string $questionnaire): self
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

    public function getFileOrder(): ?float
    {
        return $this->fileOrder;
    }

    public function setFileOrder(float $fileOrder): self
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

    public function getPartialSaveRecordOccurrence(): ?int
    {
        return $this->partialSaveRecordOccurrence;
    }

    public function setPartialSaveRecordOccurrence(?int $partialSaveRecordOccurrence): self
    {
        $this->partialSaveRecordOccurrence = $partialSaveRecordOccurrence;

        return $this;
    }

    public function getPartialSaveItemOccurrence(): ?int
    {
        return $this->partialSaveItemOccurrence;
    }

    public function setPartialSaveItemOccurrence(?int $partialSaveItemOccurrence): self
    {
        $this->partialSaveItemOccurrence = $partialSaveItemOccurrence;

        return $this;
    }

    public function getPartialSaveSubitemOccurrence(): ?int
    {
        return $this->partialSaveSubitemOccurrence;
    }

    public function setPartialSaveSubitemOccurrence(?int $partialSaveSubitemOccurrence): self
    {
        $this->partialSaveSubitemOccurrence = $partialSaveSubitemOccurrence;

        return $this;
    }


}

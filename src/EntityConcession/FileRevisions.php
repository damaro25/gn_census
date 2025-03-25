<?php

namespace App\EntityConcession;

use Doctrine\ORM\Mapping as ORM;

/**
 * FileRevisions
 *
 * @ORM\Table(name="file_revisions")})
 * @ORM\Entity
 */
class FileRevisions
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="device_id", type="text", nullable=false)
     */
    private $deviceId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="timestamp", type="integer", nullable=true)
     */
    private $timestamp;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): self
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }



}

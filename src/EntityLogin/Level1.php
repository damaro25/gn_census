<?php

namespace App\EntityLogin;

use Doctrine\ORM\Mapping as ORM;


/**
 * Level1
 * 
 * @ORM\Table(name="`level-1`")
 * @ORM\Entity
 */
class Level1
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", name="`level-1-id`")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="`case-id`")
     */
    private $caseId;

    /**
     * @ORM\Column(type="string", length=255, name="`user_login`")
     */
    private $userLogin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaseId(): ?string
    {
        return $this->caseId;
    }

    public function setCaseId(string $caseId): self
    {
        $this->caseId = $caseId;

        return $this;
    }

    public function getUserLogin(): ?string
    {
        return $this->userLogin;
    }

    public function setUserLogin(string $userLogin): self
    {
        $this->userLogin = $userLogin;

        return $this;
    }
}

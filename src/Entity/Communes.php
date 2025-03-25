<?php

namespace App\Entity;

use JsonSerializable;
use App\Repository\CommunesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommunesRepository::class)
 * @ORM\Table(name="census_communes")
 */
class Communes implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
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
     * @ORM\ManyToOne(targetEntity=Prefectures::class, inversedBy="communes")
     */
    private $prefecture;


    public function jsonSerialize():array
    {
        return array(
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'prefecture' => $this->prefecture,
            'codeParent' => $this->codeParent,
        );
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

    public function getPrefecture(): ?Prefectures
    {
        return $this->prefecture;
    }

    public function setPrefecture(?Prefectures $prefecture): self
    {
        $this->prefecture = $prefecture;

        return $this;
    }
}

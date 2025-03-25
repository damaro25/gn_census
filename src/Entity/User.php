<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users") 
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    public static $ROLE_SUPERVISOR = 'ROLE_SUPERVISOR';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActived;

    /**
     * @ORM\Column(type="string", length=9, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $passwordView;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sex;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthdate;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\OneToOne(targetEntity=Applications::class, mappedBy="account", cascade={"persist", "remove"})
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity=Lgas::class, inversedBy="users")
     */
    private $lga;

    /**
     * @ORM\ManyToOne(targetEntity=Districts::class, inversedBy="users")
     */
    private $district;

    /**
     * @Gedmo\Slug(
     *  fields={"uuid", "createAt"}, 
     *  separator="_",
     *  updatable= false,
     *  unique= false,
     *  dateFormat= "d/m/Y H-i-s",
     * )
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\OneToMany(targetEntity=Classroom::class, mappedBy="supervisor")
     */
    private $classrooms;

    /**
     * @ORM\OneToMany(targetEntity=Presentiel::class, mappedBy="supervisor")
     */
    private $presentiels;

    public function __construct()
    {
        $this->classrooms = new ArrayCollection();
        $this->presentiels = new ArrayCollection();
    }


    /**
     * @ORM\PrePersist
     */
    public function initializeColumns()
    {
        $this->isDeleted = false;
        // $this->quitusMateriel = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getname(): ?string
    {
        return $this->name;
    }

    public function setname(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getsurname(): ?string
    {
        return $this->surname;
    }

    public function setsurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getIsActived(): ?bool
    {
        return $this->isActived;
    }

    public function setIsActived(bool $isActived): self
    {
        $this->isActived = $isActived;

        return $this;
    }

    public function getphone(): ?string
    {
        return $this->phone;
    }

    public function setphone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getaddress(): ?string
    {
        return $this->address;
    }

    public function setaddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }


    public function getPasswordView(): ?string
    {
        return $this->passwordView;
    }

    public function setPasswordView(?string $passwordView): self
    {
        $this->passwordView = $passwordView;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getsex(): ?string
    {
        return $this->sex;
    }

    public function setsex(?string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getbirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setbirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getApplications(): ?Applications
    {
        return $this->applications;
    }

    public function setApplications(?Applications $applications): self
    {
        // unset the owning side of the relation if necessary
        if ($applications === null && $this->applications !== null) {
            $this->applications->setAccount(null);
        }

        // set the owning side of the relation if necessary
        if ($applications !== null && $applications->getAccount() !== $this) {
            $applications->setAccount($this);
        }

        $this->applications = $applications;

        return $this;
    }

    public function getLga(): ?Lgas
    {
        return $this->lga;
    }

    public function setLga(?Lgas $lga): self
    {
        $this->lga = $lga;

        return $this;
    }

    public function getDistrict(): ?Districts
    {
        return $this->district;
    }

    public function setDistrict(?Districts $district): self
    {
        $this->district = $district;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getTotalEnumeratorsProfile(): ?int
    {
        return array_reduce($this->classrooms->toArray(), function ($total, $pr) {
            return $total + (($pr->isIsProfile() && $pr->getDeleted() == 0) ? 1 : 0);
        }, 0);
    }

    public function checkDataAddedCsPro(): ?int
    {
        return array_reduce($this->classrooms->toArray(), function ($total, $pr) {
            return $total + ($pr->getCswebResponse() == 200  ? 1 : 0);
        }, 0);
    }

    /**
     * @return Collection<int, Classroom>
     */
    public function getClassrooms(): Collection
    {
        return $this->classrooms;
    }

    public function addClassroom(Classroom $classroom): self
    {
        if (!$this->classrooms->contains($classroom)) {
            $this->classrooms[] = $classroom;
            $classroom->setSupervisor($this);
        }

        return $this;
    }

    public function removeClassroom(Classroom $classroom): self
    {
        if ($this->classrooms->removeElement($classroom)) {
            // set the owning side to null (unless already changed)
            if ($classroom->getSupervisor() === $this) {
                $classroom->setSupervisor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Presentiel>
     */
    public function getPresentiels(): Collection
    {
        return $this->presentiels;
    }

    public function addPresentiel(Presentiel $presentiel): self
    {
        if (!$this->presentiels->contains($presentiel)) {
            $this->presentiels[] = $presentiel;
            $presentiel->setSupervisor($this);
        }

        return $this;
    }

    public function removePresentiel(Presentiel $presentiel): self
    {
        if ($this->presentiels->removeElement($presentiel)) {
            // set the owning side to null (unless already changed)
            if ($presentiel->getSupervisor() === $this) {
                $presentiel->setSupervisor(null);
            }
        }

        return $this;
    }


    public function btnColor(): string
    {
        if ($this->checkDataAddedCsPro() == 0) {
            return 'danger';
        } else if ($this->checkDataAddedCsPro() == $this->getTotalEnumeratorsProfile()) {
            return 'primary';
        } else {
            return 'warning';
        }
    }



    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'surname' => $this->surname,
            'name' => $this->name,
            'login' => $this->username,
            'member' => $this->name . " " . $this->surname,
            'password' => $this->passwordView ? $this->passwordView : "",
            'phone' => $this->phone ? $this->phone : "",
            'address' => $this->address ? $this->address : "",
            'role' => $this->roles ? substr($this->roles[0], 5) : "",
            'e_mail' => $this->mail ? $this->mail : "",
            'username' => $this->username,
            'passwordView' => $this->passwordView,
            'startAt' => $this->startAt ? $this->startAt->format('d/m/Y') : "",
            'endAt' => $this->endAt ? $this->endAt->format('d/m/Y') : "",
            'isActive' => $this->isActived,
            'roles' => $this->roles[0],
            'isActived' => $this->isActived,
            'createAt' => $this->createAt ? $this->createAt->format('d/m/Y') : "",
            'birthdate' => $this->birthdate != NULL ? $this->birthdate->format("Y-m-d") : "",
            'slug' => $this->slug,
            'lga' => $this->lga,
            'cptEnumerators' => count($this->classrooms),
            'cptProfile' => $this->getTotalEnumeratorsProfile(),
            'checkcsPro' => $this->btnColor()

        );
    }
}

<?php

namespace App\EntityLogin;

use Doctrine\ORM\Mapping as ORM;


/**
 * ext_log_rec

 * @ORM\Table(name="`ext_log_rec`")
 * @ORM\Entity
 */
class ExtLoginRec
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", name="`ext_log_rec-id`")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Level1::class)
     * @ORM\JoinColumn(nullable=false, referencedColumnName="`level-1-id`",name="`level-1-id`")
     */
    private $level1id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $userPass;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $userFirstname;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $userLastname;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userLastconnect;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userPhonegphc;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $userEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userZone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userDr;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userDepartment;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userInfo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userNumDossier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $initUser;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sync_controle_count;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wk_lat;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wk_lon;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $service_fait;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lunch_authorized;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type_remplacement;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $login_remplacant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $is_closed;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel1id(): ?Level1
    {
        return $this->level1id;
    }

    public function setLevel1id(Level1 $level1id): self
    {
        $this->level1id = $level1id;

        return $this;
    }

    public function getUserPass(): ?string
    {
        return $this->userPass;
    }

    public function setUserPass(?string $userPass): self
    {
        $this->userPass = $userPass;

        return $this;
    }

    public function getUserFirstname(): ?string
    {
        return $this->userFirstname;
    }

    public function setUserFirstname(?string $userFirstName): self
    {
        $this->userFirstname = $userFirstName;

        return $this;
    }

    public function getUserLastname(): ?string
    {
        return $this->userLastname;
    }

    public function setUserLastname(?string $userLastname): self
    {
        $this->userLastname = $userLastname;

        return $this;
    }

    public function getUserLastconnect(): ?int
    {
        return $this->userLastconnect;
    }

    public function setUserLastconnect(?int $userLastconnect): self
    {
        $this->userLastconnect = $userLastconnect;

        return $this;
    }

    public function getUserStatus(): ?int
    {
        return $this->userStatus;
    }

    public function setUserStatus(?int $userStatus): self
    {
        $this->userStatus = $userStatus;

        return $this;
    }

    public function getUserPhonegphc(): ?int
    {
        return $this->userPhonegphc;
    }

    public function setUserPhonegphc(?int $userPhonegphc): self
    {
        $this->userPhonegphc = $userPhonegphc;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(?string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    public function getUserZone(): ?string
    {
        return $this->userZone;
    }

    public function setUserZone(?string $userZone): self
    {
        $this->userZone = $userZone;

        return $this;
    }

    public function getUserDr(): ?string
    {
        return $this->userDr;
    }

    public function setUserDr(?string $userDr): self
    {
        $this->userDr = $userDr;

        return $this;
    }

    public function getUserDepartment(): ?string
    {
        return $this->userDepartment;
    }

    public function setUserDepartment(?string $userDepartment): self
    {
        $this->userDepartment = $userDepartment;

        return $this;
    }

    public function getUserType(): ?int
    {
        return $this->userType;
    }

    public function setUserType(?int $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getUserInfo(): ?string
    {
        return $this->userInfo;
    }

    public function setUserInfo(?string $userInfo): self
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    public function getUserNumDossier(): ?string
    {
        return $this->userNumDossier;
    }

    public function setUserNumDossier(?string $userNumDossier): self
    {
        $this->userNumDossier = $userNumDossier;

        return $this;
    }

    public function getInitUser(): ?int
    {
        return $this->initUser;
    }

    public function setInitUser(?int $initUser): self
    {
        $this->initUser = $initUser;

        return $this;
    }

    public function getSyncControleCount(): ?int
    {
        return $this->sync_controle_count;
    }

    public function setSyncControleCount(?int $sync_controle_count): self
    {
        $this->sync_controle_count = $sync_controle_count;

        return $this;
    }

    public function getWkLat(): ?int
    {
        return $this->wk_lat;
    }

    public function setWkLat(?int $wk_lat): self
    {
        $this->wk_lat = $wk_lat;

        return $this;
    }

    public function getWkLon(): ?int
    {
        return $this->wk_lon;
    }

    public function setWkLon(?int $wk_lon): self
    {
        $this->wk_lon = $wk_lon;

        return $this;
    }

    public function getServiceFait(): ?int
    {
        return $this->service_fait;
    }

    public function setServiceFait(?int $service_fait): self
    {
        $this->service_fait = $service_fait;

        return $this;
    }

    public function getLunchAuthorized(): ?int
    {
        return $this->lunch_authorized;
    }

    public function setLunchAuthorized(?int $lunch_authorized): self
    {
        $this->lunch_authorized = $lunch_authorized;

        return $this;
    }

    public function getTypeRemplacement(): ?int
    {
        return $this->type_remplacement;
    }

    public function setTypeRemplacement(?int $type_remplacement): self
    {
        $this->type_remplacement = $type_remplacement;

        return $this;
    }

    public function getLoginRemplacant(): ?int
    {
        return $this->login_remplacant;
    }

    public function setLoginRemplacant(?int $login_remplacant): self
    {
        $this->login_remplacant = $login_remplacant;

        return $this;
    }

    public function getIsClosed(): ?int
    {
        return $this->is_closed;
    }

    public function setIsClosed(?int $is_closed): self
    {
        $this->is_closed = $is_closed;

        return $this;
    }
}

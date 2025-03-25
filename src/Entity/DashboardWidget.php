<?php

namespace App\Entity;

use App\Repository\DashboardWidgetRepository;
use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DashboardWidgetRepository::class)
 * @ORM\Table(name="census_dashboard_widget")
 */
class DashboardWidget implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $taille;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $couleur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="text")
     */
    private $requete;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $op_saisie;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $profils = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $taille_mobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $couleur_hover;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $couleur_min;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $couleur_max;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $carte;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $join_by;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $valeur_null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $min;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $max;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $unite_mesure;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom_carte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $datasets;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $colonnes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $requete_filter_1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $requete_filter_2;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $requete_filter_3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dashboard;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $couleur_droite;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $couleur_gauche;


    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'statut' => ($this->actif == TRUE) ? 'Actif' : 'Desactivé',
            'libelle' => $this->libelle,
            'type' => $this->type,
            'taille' => $this->taille,
            'taille_mobile' => $this->taille_mobile,
            'couleur' => $this->couleur,
            'couleur_gauche' => $this->couleur_gauche,
            'couleur_droite' => $this->couleur_droite,
            'datasets' => $this->datasets,
            'icone' => $this->icone,
            'requete' => $this->requete,
            'utilisateur' => $this->op_saisie->getSurname() . " " . $this->op_saisie->getName(),
            'roles' => $this->profils ? substr($this->profils[0], 5) : "",
            'profils' => $this->profils,
            'createAt' => $this->created_at ? $this->created_at->format('d/m/Y') : "",
            'page' => $this->getPageName(),
            'dashboard' => $this->dashboard,
            'status_check' => $this->actif,
            'sql' => $this->requete,
            'sql1' => $this->requete_filter_1,
            'sql2' => $this->requete_filter_2,
            'sql3' => $this->requete_filter_3,
            'colonnes' => $this->colonnes,
            'unite_mesure' => $this->unite_mesure,
            'min' => $this->min,
            'max' => $this->max,
            'join_by' => $this->join_by,
            'for_null' => $this->valeur_null,
            'color_min' => $this->couleur_min,
            'color_max' => $this->couleur_max,
            'color_hover' => $this->couleur_hover,
            'carte' => $this->carte,
            'nom_carte' => $this->nom_carte,
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(int $taille): self
    {
        $this->taille = $taille;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): self
    {
        $this->icone = $icone;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getRequete(): ?string
    {
        return $this->requete;
    }

    public function setRequete(string $requete): self
    {
        $this->requete = $requete;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getOpSaisie(): ?User
    {
        return $this->op_saisie;
    }

    public function setOpSaisie(?User $op_saisie): self
    {
        $this->op_saisie = $op_saisie;

        return $this;
    }

    public function getProfils(): ?array
    {
        return $this->profils;
    }

    public function setProfils(?array $profils): self
    {
        $this->profils = $profils;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTime $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getTailleMobile(): ?int
    {
        return $this->taille_mobile;
    }

    public function setTailleMobile(?int $taille_mobile): self
    {
        $this->taille_mobile = $taille_mobile;

        return $this;
    }

    public function getCouleurHover(): ?string
    {
        return $this->couleur_hover;
    }

    public function setCouleurHover(?string $couleur_hover): self
    {
        $this->couleur_hover = $couleur_hover;

        return $this;
    }

    public function getCouleurMin(): ?string
    {
        return $this->couleur_min;
    }

    public function setCouleurMin(?string $couleur_min): self
    {
        $this->couleur_min = $couleur_min;

        return $this;
    }

    public function getCouleurMax(): ?string
    {
        return $this->couleur_max;
    }

    public function setCouleurMax(?string $couleur_max): self
    {
        $this->couleur_max = $couleur_max;

        return $this;
    }

    public function getCarte(): ?string
    {
        return $this->carte;
    }

    public function setCarte(?string $carte): self
    {
        $this->carte = $carte;

        return $this;
    }

    public function getJoinBy(): ?string
    {
        return $this->join_by;
    }

    public function setJoinBy(?string $join_by): self
    {
        $this->join_by = $join_by;

        return $this;
    }

    public function getValeurNull(): ?string
    {
        return $this->valeur_null;
    }

    public function setValeurNull(?string $valeur_null): self
    {
        $this->valeur_null = $valeur_null;

        return $this;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function getUniteMesure(): ?string
    {
        return $this->unite_mesure;
    }

    public function setUniteMesure(?string $unite_mesure): self
    {
        $this->unite_mesure = $unite_mesure;

        return $this;
    }

    public function getNomCarte(): ?string
    {
        return $this->nom_carte;
    }

    public function setNomCarte(?string $nom_carte): self
    {
        $this->nom_carte = $nom_carte;

        return $this;
    }

    public function getDatasets(): ?string
    {
        return $this->datasets;
    }

    public function setDatasets(?string $datasets): self
    {
        $this->datasets = $datasets;

        return $this;
    }

    public function getColonnes(): ?string
    {
        return $this->colonnes;
    }

    public function setColonnes(?string $colonnes): self
    {
        $this->colonnes = $colonnes;

        return $this;
    }

    public function getRequeteFilter1(): ?string
    {
        return $this->requete_filter_1;
    }

    public function setRequeteFilter1(?string $requete_filter_1): self
    {
        $this->requete_filter_1 = $requete_filter_1;

        return $this;
    }

    public function getRequeteFilter2(): ?string
    {
        return $this->requete_filter_2;
    }

    public function setRequeteFilter2(?string $requete_filter_2): self
    {
        $this->requete_filter_2 = $requete_filter_2;

        return $this;
    }

    public function getRequeteFilter3(): ?string
    {
        return $this->requete_filter_3;
    }

    public function setRequeteFilter3(?string $requete_filter_3): self
    {
        $this->requete_filter_3 = $requete_filter_3;

        return $this;
    }

    public function getDashboard(): ?string
    {
        return $this->dashboard;
    }

    public function setDashboard(?string $dashboard): self
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    public function getCouleurDroite(): ?string
    {
        return $this->couleur_droite;
    }

    public function setCouleurDroite(?string $couleur_droite): self
    {
        $this->couleur_droite = $couleur_droite;

        return $this;
    }

    public function getCouleurGauche(): ?string
    {
        return $this->couleur_gauche;
    }

    public function setCouleurGauche(?string $couleur_gauche): self
    {
        $this->couleur_gauche = $couleur_gauche;

        return $this;
    }

    public function getPageName(): String
    {
        $page = $this->dashboard;
        if($page == 'principal'){
            $page = 'T. Bord Principal';
        }else if($page == 'interviews'){
            $page = 'Stat. Interviews';
        }else if($page == 'concretisation'){
            $page = 'Concrétisation';
        }else if($page == 'cles'){
            $page = 'Indic. clés';
        }else if($page == 'qualite'){
            $page = 'Indic. De Qualité';
        }else if($page == 'rapports'){
            $page = 'Rapports';
        }

        return $page;
    }

}

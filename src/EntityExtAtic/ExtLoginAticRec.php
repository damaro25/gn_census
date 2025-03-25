<?php

namespace App\EntityExtAtic;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExtLoginAticRec
 *
 * @ORM\Table(name="ext_login_atic_rec")
 * @ORM\Entity
 */
class ExtLoginAticRec
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer",name="ext_login_atic_rec_id")
     */
    private $ext_login_atic_rec_id;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ext_mdp_atic;

    /**
     * @ORM\Column(type="text", name="prenom_s_et_nom_atic", nullable=true)
     */
    private $prenom_s_et_nom_atic;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $departement;


    public function getId()
    {
        return $this->ext_login_atic_rec_id;
    }

    public function setId($id)
    {
        $this->ext_login_atic_rec_id = $id;

        return $this;
    }

    /**
     * Get the value of level1Id
     *
     * @return  int
     */
    public function getLevel1Id()
    {
        return $this->level1Id;
    }

    public function setLevel1Id($level1Id)
    {
        $this->level1Id = $level1Id;

        return $this;
    }

    public function getExtNomComplet()
    {
        return $this->prenom_s_et_nom_atic;
    }

    public function setExtNomComplet($prenom_s_et_nom_atic)
    {
        $this->prenom_s_et_nom_atic = $prenom_s_et_nom_atic;

        return $this;
    }

    public function getDepartement()
    {
        return $this->departement;
    }

    public function setDepartement($departement)
    {
        $this->departement = $departement;

        return $this;
    }


    public function getPassword()
    {
        return $this->ext_mdp_atic;
    }

    public function setPassword($ext_mdp_atic)
    {
        $this->ext_mdp_atic = $ext_mdp_atic;

        return $this;
    }
}

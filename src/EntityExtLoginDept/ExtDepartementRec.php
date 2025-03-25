<?php

namespace App\EntityExtLoginDept;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExtDepartementRec
 *
 * @ORM\Table(name="ext_departement_rec")
 * @ORM\Entity
 */
class ExtDepartementRec
{

   /**
    * @var int
     * @ORM\Id
     * @ORM\Column(type="integer",name="ext_departement_rec_id")
     */
    private $ext_departement_rec_id;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id; 

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ext_mot_de_passe;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ext_prenom;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ext_nom;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ext_date_recup;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ext_user_type;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ext_log_file_is_downloaded;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ext_quitus_delivered;

    
    public function getId()
    {
        return $this->ext_departement_rec_id;
    }

    public function setId($id)
    {
        $this->ext_departement_rec_id = $id;

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

    public function getExtPrenom()
    {
        return $this->ext_prenom;
    }

    public function setExtPrenom($ext_prenom)
    {
        $this->ext_prenom = $ext_prenom;

        return $this;
    }

    public function getExtNom()
    {
        return $this->ext_nom;
    }

    public function setExtNom($ext_nom)
    {
        $this->ext_nom = $ext_nom;

        return $this;
    }

    public function getExtDateRecup()
    {
        return $this->ext_date_recup;
    }

    public function setExtDateRecup($ext_date_recup)
    {
        $this->ext_date_recup = $ext_date_recup;

        return $this;
    }

    public function getExtQuitusDelivered()
    {
        return $this->ext_quitus_delivered;
    }

    public function setExtQuitusDelivered($ext_quitus_delivered)
    {
        $this->ext_quitus_delivered = $ext_quitus_delivered;

        return $this;
    }

    public function getExtLogFileIsDownloaded()
    {
        return $this->ext_log_file_is_downloaded;
    }

    public function setExtLogFileIsDownloaded($ext_log_file_is_downloaded)
    {
        $this->ext_log_file_is_downloaded = $ext_log_file_is_downloaded;

        return $this;
    }

    public function getExtUserType()
    {
        return $this->ext_user_type;
    }

    public function setExtUserType($ext_user_type)
    {
        $this->ext_user_type = $ext_user_type;

        return $this;
    }

    public function getPassword()
    {
        return $this->ext_mot_de_passe;
    }

    public function setPassword($ext_mot_de_passe)
    {
        $this->ext_mot_de_passe = $ext_mot_de_passe;

        return $this;
    }
    
}

<?php

namespace App\EntityApplication;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClasseRec
 *
 * @ORM\Table(name="classe_rec")
 * @ORM\Entity
 */
class ClasseRec
{

   /**
    * @var int
     * @ORM\Id
     * @ORM\Column(type="integer",name="classe_rec_id")
     */
    private $classeRecId;

    /**
     * @var int
     *
     * @ORM\Column(name="level_1_id", type="integer", nullable=false)
     */
    private $level1Id; 

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $commune;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $commune2;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $commune3;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $prenom;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sexe;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dateNaissance;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lieuNaissance;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $situationMatrimoniale;


     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $adresse;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $telephone;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $telephone2;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nin;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $diplome;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $langue1;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $langue2;

   /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $langue3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $informatique;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recensement;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $enqueteDigital;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $enquete;


     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $posteSouhaite;

     /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $telephoneWhatsapp;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jourPresence;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $noteFinale;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */

    private $nombre_enquetes;

    /**
     * @ORM\Column(type="integer", nullable=true)
    */

    private $nombre_recens;

     /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $certif_ansf;
    

    /**
     * Get the value of noteFinale
     */ 
    public function getNoteFinale()
    {
        return $this->noteFinale;
    }

    /**
     * Set the value of noteFinale
     *
     * @return  self
     */ 
    public function setNoteFinale($noteFinale)
    {
        $this->noteFinale = $noteFinale;

        return $this;
    }

    /**
     * Get the value of jourPresence
     */ 
    public function getJourPresence()
    {
        return $this->jourPresence;
    }

    /**
     * Set the value of jourPresence
     *
     * @return  self
     */ 
    public function setJourPresence($jourPresence)
    {
        $this->jourPresence = $jourPresence;

        return $this;
    }

    /**
     * Get the value of telephoneWhatsapp
     */ 
    public function getTelephoneWhatsapp()
    {
        return $this->telephoneWhatsapp;
    }

    /**
     * Set the value of telephoneWhatsapp
     *
     * @return  self
     */ 
    public function setTelephoneWhatsapp($telephoneWhatsapp)
    {
        $this->telephoneWhatsapp = $telephoneWhatsapp;

        return $this;
    }

    /**
     * Get the value of posteSouhaite
     */ 
    public function getPosteSouhaite()
    {
        return $this->posteSouhaite;
    }

    /**
     * Set the value of posteSouhaite
     *
     * @return  self
     */ 
    public function setPosteSouhaite($posteSouhaite)
    {
        $this->posteSouhaite = $posteSouhaite;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of enquete
     */ 
    public function getEnquete()
    {
        return $this->enquete;
    }

    /**
     * Set the value of enquete
     *
     * @return  self
     */ 
    public function setEnquete($enquete)
    {
        $this->enquete = $enquete;

        return $this;
    }

    /**
     * Get the value of classeRecId
     *
     * @return  int
     */ 
    public function getClasseRecId()
    {
        return $this->classeRecId;
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

    /**
     * Get the value of enqueteDigital
     */ 
    public function getEnqueteDigital()
    {
        return $this->enqueteDigital;
    }

    /**
     * Set the value of enqueteDigital
     *
     * @return  self
     */ 
    public function setEnqueteDigital($enqueteDigital)
    {
        $this->enqueteDigital = $enqueteDigital;

        return $this;
    }

    /**
     * Get the value of recensement
     */ 
    public function getRecensement()
    {
        return $this->recensement;
    }

    /**
     * Set the value of recensement
     *
     * @return  self
     */ 
    public function setRecensement($recensement)
    {
        $this->recensement = $recensement;

        return $this;
    }

    /**
     * Get the value of informatique
     */ 
    public function getInformatique()
    {
        return $this->informatique;
    }

    /**
     * Set the value of informatique
     *
     * @return  self
     */ 
    public function setInformatique($informatique)
    {
        $this->informatique = $informatique;

        return $this;
    }

    /**
     * Get the value of diplome
     */ 
    public function getDiplome()
    {
        return $this->diplome;
    }

    /**
     * Set the value of diplome
     *
     * @return  self
     */ 
    public function setDiplome($diplome)
    {
        $this->diplome = $diplome;

        return $this;
    }

    /**
     * Get the value of commune
     */ 
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set the value of commune
     *
     * @return  self
     */ 
    public function setCommune($commune)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get the value of commune2
     */ 
    public function getCommune2()
    {
        return $this->commune2;
    }

    /**
     * Set the value of commune2
     *
     * @return  self
     */ 
    public function setCommune2($commune2)
    {
        $this->commune2 = $commune2;

        return $this;
    }

    /**
     * Get the value of dateNaissance
     */ 
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Set the value of dateNaissance
     *
     * @return  self
     */ 
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get the value of prenom
     */ 
    public function getName()
    {
        return $this->prenom;
    }

    /**
     * Set the value of prenom
     *
     * @return  self
     */ 
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get the value of adresse
     */ 
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set the value of adresse
     *
     * @return  self
     */ 
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get the value of lieuNaissance
     */ 
    public function getLieuNaissance()
    {
        return $this->lieuNaissance;
    }

    /**
     * Set the value of lieuNaissance
     *
     * @return  self
     */ 
    public function setLieuNaissance($lieuNaissance)
    {
        $this->lieuNaissance = $lieuNaissance;

        return $this;
    }

    /**
     * Get the value of situationMatrimoniale
     */ 
    public function getSituationMatrimoniale()
    {
        return $this->situationMatrimoniale;
    }

    /**
     * Set the value of situationMatrimoniale
     *
     * @return  self
     */ 
    public function setSituationMatrimoniale($situationMatrimoniale)
    {
        $this->situationMatrimoniale = $situationMatrimoniale;

        return $this;
    }

    /**
     * Get the value of nin
     */ 
    public function getNin()
    {
        return $this->nin;
    }

    /**
     * Set the value of nin
     *
     * @return  self
     */ 
    public function setNin($nin)
    {
        $this->nin = $nin;

        return $this;
    }

    /**
     * Get the value of commune3
     */ 
    public function getCommune3()
    {
        return $this->commune3;
    }

    /**
     * Set the value of commune3
     *
     * @return  self
     */ 
    public function setCommune3($commune3)
    {
        $this->commune3 = $commune3;

        return $this;
    }

    /**
     * Get the value of nom
     */ 
    public function getSurname()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     *
     * @return  self
     */ 
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get the value of sexe
     */ 
    public function getSex()
    {
        return $this->sexe;
    }

    /**
     * Set the value of sexe
     *
     * @return  self
     */ 
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get the value of telephone
     */ 
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set the value of telephone
     *
     * @return  self
     */ 
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get the value of telephone2
     */ 
    public function getTelephone2()
    {
        return $this->telephone2;
    }

    /**
     * Set the value of telephone2
     *
     * @return  self
     */ 
    public function setTelephone2($telephone2)
    {
        $this->telephone2 = $telephone2;

        return $this;
    }

    /**
     * Get the value of langue1
     */ 
    public function getLangue1()
    {
        return $this->langue1;
    }

    /**
     * Set the value of langue1
     *
     * @return  self
     */ 
    public function setLangue1($langue1)
    {
        $this->langue1 = $langue1;

        return $this;
    }

    /**
     * Get the value of langue2
     */ 
    public function getLangue2()
    {
        return $this->langue2;
    }

    /**
     * Set the value of langue2
     *
     * @return  self
     */ 
    public function setLangue2($langue2)
    {
        $this->langue2 = $langue2;

        return $this;
    }

    /**
     * Get the value of langue3
     */ 
    public function getLangue3()
    {
        return $this->langue3;
    }

    /**
     * Set the value of langue3
     *
     * @return  self
     */ 
    public function setLangue3($langue3)
    {
        $this->langue3 = $langue3;

        return $this;
    }

    /**
     * Set the value of level1Id
     *
     * @param  int  $level1Id
     *
     * @return  self
     */ 
    public function setLevel1Id(int $level1Id)
    {
        $this->level1Id = $level1Id;

        return $this;
    }

    /**
     * Set the value of classeRecId
     *
     * @param  int  $classeRecId
     *
     * @return  self
     */ 
    public function setClasseRecId(int $classeRecId)
    {
        $this->classeRecId = $classeRecId;

        return $this;
    }

    // Nouvelle
    public function getSurnamebreEnquetes()
    {
        return $this->nombre_enquetes;
    }

    public function setNombreEnquetes($nombre_enquetes)
    {
        $this->nombre_enquetes = $nombre_enquetes;

        return $this;
    }

    public function getSurnamebreRecensement()
    {
        return $this->nombre_recens;
    }

    public function setNombreRecensement($nombre_recens)
    {
        $this->nombre_recens = $nombre_recens;

        return $this;
    }

    public function getSurnamebreCertifsGbos()
    {
        return $this->certif_ansf;
    }

    public function setNombreCertifsGbos($certif_ansf)
    {
        $this->certif_ansf = $certif_ansf;

        return $this;
    }
}

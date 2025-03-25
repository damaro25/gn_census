<?php


namespace App\PDF;

use DateTime;
use DateTimeInterface;
use IntlDateFormatter;
use Symfony\Component\HttpKernel\KernelInterface;
use TCPDF;

class AttestationAgent extends TCPDF
{
    private static $X = 10;
    private static $Y = 90;
    private $attestation ;



    public function __construct(
        KernelInterface $kernel ,
        string  $title ,
        array $attestation
        )
    {
         // Include the main TCPDF library (search for installation path).
        // require_once($kernel->getProjectDir() . '/vendor/tecnickcom/tcpdf/examples/tcpdf_include.php');
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->attestation = $attestation;
        $this->SetProtection(['modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'], '','12511985',0, null);
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('CensusMP GBOS');
        $this->SetTitle($title);
        $this->SetSubject('CensusMP');
        $this->SetKeywords('GBOS, CensusMP');

        // remove default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set auto page breaks
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $this->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $this->setFontSubsetting(true);

        // set font
        $this->SetFont('helvetica', '', 11);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $this->AddPage();


        @$this->Image(__DIR__.'/bordure_attestation.png', 1 , 1, $this->getPageWidth()+20, $this->getPageHeight(), 'PNG','','C',true);

        // set text shadow effect
        $this->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // gambia
        $numero = \str_pad($this->attestation['idEnquete'].$this->attestation['id'] , 10 ,'0',STR_PAD_LEFT);
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/gambia.png', 10, 10, 50, 30, 'PNG');
        $this->write(25, "N° $numero  MEPC/GBOS/DAGRH/DRH/BAP", '', false, "R");

        // set logo GBOS
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_grille.png', 10, 50, 40, 30, 'PNG');

        $fmt = datefmt_create(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Africa/Dakar',
            IntlDateFormatter::GREGORIAN
        );
        $this->setX(90);
        $this->setY(35);
        $jour =  datefmt_format($fmt, time());
        $this->write(25, "Dakar , le $jour", '', false, "C");
        

        $this->setX(10);
        $this->setY(83);
        $this->writeHTML("<h5>Direction de l’Administration générale <br/> et des Ressources Humaines</h5>", '', false, "L");

        $this->setX(100);
        $this->setY(80);
        $this->write(25, "La Directrice P.I.            ", '', false, "R");

        

        $this->setX(100);
        $this->setY(80);
        $this->writeHTMLCell(100 , 5 , 60 , 120 , "",0,0 );
        $img = @$this->Image(__DIR__ . '/titre_attestation.png', 60, 110, 100, 10, 'PNG','','C',true);

        
        $this->setX(10);
        $this->setY(140);
        $nom = $this->attestation['prenom'].' '.$this->attestation['nom'] ;
        $sexe = $this->attestation['sexe'] == 'Masculin' ? 'Monsieur' : ($this->attestation['sexe'] == 'Feminin'? 'Madame': 'Monsieur/Madame');
        $dateNaissance = $this->attestation['dateNaissance'] != null  && DateTime::createFromFormat('d/m/Y', $this->attestation['dateNaissance']) !=  false ? 'né(e) le  '.DateTime::createFromFormat('d/m/Y', $this->attestation['dateNaissance'])->format('d-m-Y')  : '' ;
        $lieuNaissance = $this->attestation['lieuNaissance'] != null ?( " à ". $this->attestation['lieuNaissance'] ):  '';
        $poste = $this->attestation['nomCategorie'];
        $projet = $this->attestation['nomEnquete'];
        $idcategorie=$this->attestation['idCategorie'];
        $d='';
        if ($idcategorie == 1 || $idcategorie == 2 || $idcategorie == 3) {
            $d="d'";
        }else {
            $d="de ";
        }
       
        $content = "
        La Directrice, par intérim, de l’Administration générale et des Ressources humaines 
        (DAGRH) de l'Agence Nationale de la Statistique et de la Démographie <b>(GBOS)</b> , 
        atteste que $sexe <b> $nom </b> $dateNaissance  $lieuNaissance, a bénéficié de contrat 
        de prestation de services en qualité $d<b>$poste</b> dans le cadre du projet suivant : $projet";
        $this->writeHTML($content, true, false, false, false, 'L'); 
   
       
       
        if($this->attestation['dateDebutEnquete'] && $this->attestation['dateFinEnquete'] ){
            $du = $this->attestation['dateDebutEnquete'] ;
            $au = $this->attestation['dateFinEnquete'] ;
            $this->setX(10);
            $this->setY(170);
            $this->writeHTML("<b>Projet</b> du <b>$du</b> au <b>$au</b>.", true, false, false, false, 'C');
        }
       

        $this->setX(10);
        $this->setY(180);
        $this->writeHTML("La présente attestation lui est délivrée pour servir valoir ce que de droit.", true, false, false, false, 'C');
   

        $img = @$this->Image(__DIR__ . '/signature.png', 90, 190, 70, 40, 'PNG','','C');

        $this->setX(10);
        $this->setY(230);
        $this->writeHTML("<b>Hawa SAMBA</b>.", true, false, false, false, 'C');


        $img = @$this->Image(__DIR__ . '/pied_page.png', 9, 240, $this->getPageWidth()-22, 10, 'PNG','','C');
    }

    

   
}

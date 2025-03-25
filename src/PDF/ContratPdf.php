<?php

declare(strict_types=1);

namespace App\PDF;

use TCPDF;
use DateTime;
use App\Entity\User;
use NumberFormatter;
use App\Entity\Departements;
use App\Repository\UserRepository;
use App\Repository\SallesRepository;
use App\Repository\CompositionRepository;
use App\Repository\SalaireBaseRepository;
use App\Repository\ApplicationsRepository;
use App\Repository\ContratParamRepository;
use App\Repository\DepartementsRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\CommunesArrCommunautesRurales;
use Symfony\Component\HttpKernel\KernelInterface;

class ContratPdf extends TCPDF
{
    private static $X = 10;
    private static $Y = 90;
    private $sup = "";


    public function __construct(KernelInterface $kernel, $title)
    {
        // Include the main TCPDF library (search for installation path).
        // require_once($kernel->getProjectDir() . '/vendor/tecnickcom/tcpdf/examples/tcpdf_include.php');
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('CensusMP GBOS');
        $this->SetTitle($title);
        $this->SetSubject('CensusMP');
        $this->SetKeywords('GBOS, CensusMP');

        // remove default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(true);
        // $this->Footer();
        // $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // $this->SetFooterMargin(PDF_MARGIN_FOOTER);

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

        // $this->SetAutoPageBreak(TRUE, 0);

        // set text shadow effect
        $this->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // gambia
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/gambia.png', 10, 10, 50, 30, 'PNG');
        $this->write(25, "N°_________________________ MEPC/GBOS/DG/UMO-RGPH-5/SRH", '', false, "R");

        // set logo GBOS
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_grille.png', 10, 50, 40, 30, 'PNG');
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png', 10, 50, 40, 30, 'PNG', '', 'T', false, 300, 'R', false, false, 0,     false, false, false);

        $this->setX(10);
        $this->setY(70);
        $this->write(25, "Je suis recensé (e), je compte !", '', false, "R");

        // $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png', 20, 47, 30, 30, 'PNG', '', 'T', false, 170, 'R', false, false, 0,     false, false, false);

        // $this->setX(10);
        // $this->setY(70);
        // $this->write(25, "Je suis recensé (e), je compte !", '', false, "R");

        $this->setX(10);
        $this->setY(90);
        
        $this->writeHTML("<h4>$title</h4>", true, false, false, false, 'C');
        $this->SetY($this->GetY() + 5);
        // $this->setCellPaddings(0, , 0, 0);
        //$this->SetMargins(10, 55, 10, true);
        //$this->SetTopMargin(120);
    }

    /**
     * add an table html
     */
    public function addTable($titles, $data, $titre = NULL, $superviseur = "")
    {
        $this->sup = $superviseur;
        $j = 0;
        $header = implode("",  array_map(function ($h)  use (&$j) {
            $j += 1;

            if ($j == 1) {
                $buildH =  '<th style="width: 35px;">' . ucfirst($h) . "</th>";
            } else if ($j == 2 || $j == 3) {
                $buildH =  '<th style="width: 210px;">' . ucfirst($h) . "</th>";
            } else {
                $buildH =  "<th>" . ucfirst($h) . "</th>";
            }
            // return "<th>" . ucfirst($h) . "</th>";
            return $buildH;
        }, $titles));
        $rows =  '';

       $i = 0;
       foreach ($data as $line) {
            $tr = implode("",  array_map(function ($cell)  use (&$i) {
                $i += 1;

                if ($i == 1) {
                    $buildtr = '<td style="width: 35px;">' . $cell . "</td>";
                } else if ($i == 2 || $i == 3) {
                    $buildtr = '<td style="width: 210px;">' . $cell . "</td>";
                } else {
                    $buildtr = "<td>" . $cell . "</td>";
              }
               return $buildtr;
          }, $line));

            $rows .= <<<EOD
                <tr style="font-size: 12px;">
                    $tr 
                </tr> 
            EOD;
        }

        $tbl = <<<EOD
            <style> td { text-align: center; } th {font-weight: bold;}</style>
            <div style="align:center">
            <table border="1" style="width: 100%;" cellspacing="0" cellpadding="5">
                <tr style="font-size: 12px; text-align: center;">
                    $header
                </tr>    
                $rows
            </table>
            </div>
        EOD;

        if ($titre != NULL) {
            $this->writeHTML("<h3>$titre</h3>", true, false, true, false, 'C');
            $this->SetY($this->GetY() + 5);
        }
        $this->writeHTML($tbl, true, false, false, false, '');
    }

    public static function modelContratAR(
        KernelInterface $kernel,
        User $acteur,
        ApplicationsRepository $applicationsRepository,
        ContratParamRepository $repo,
        SalaireBaseRepository $salaireBaseRepository
    ) {
        // $departement = $acteur->getDepartement()->getSurname();
        $region = $acteur->getDepartement()->getRegion()->getSurname();
        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        $prenom = strtoupper($acteur->getName());
        $nom = strtoupper($acteur->getSurname());
        $cni = $acteur->getCni();
        $datenaiss = $candidat->getDateNaissance()->format("d/m/Y");
        $lieunaiss = $candidat->getLieuNaissance();
        $adresse = $acteur->getAdresse();

        // informations globales Contrat
        $contratParam = $repo->findAll()[0];
        $dureeContrat = $contratParam->getDureeCollecte();
        $jrMonthYea = $dureeContrat == 1 ? $contratParam->getJrMonthYear() : $contratParam->getJrMonthYear() . "s";
        // $datePriseService = $contratParam->getDatepriseAt()->format("d/m/Y");
        // $structureAcceuil = $contratParam->getAcceuiliAla();
        // $sousLaCoordination = $contratParam->getSousLaCoordination();
        // 2 => deux, 5 => cinq
        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        $numToLetter = $dureeContrat == 1 ? "d'un" : "de " . $f->format($dureeContrat);
        $numToLetterReal = $f->format($dureeContrat);

        // salaire de base
        $salaireAR = 0;
        $salaires = $salaireBaseRepository->findAll();
        $salaireToLetter = "";

        foreach ($salaires as $sal) {
            if ($sal->getProfil() == "ROLE_AR") {
                $sa = floatval(trim((string) $sal->getMontant()));
                $salaireAR = number_format($sa, 0, '.', ' ');
                $salaireToLetter = $f->format($sal->getMontant());
            }
        }

        $pdf = new ContratPdf($kernel, 'CONTRAT DE PRESTATION DE SERVICES');

        $html =  <<<EOD
        <p class="c19">
            <p><strong>Le présent contrat est conclu : </strong> 
                <br><br><strong>ENTRE,</strong> 
                <br><br>L’Agence nationale de la Statistique et de la Démographie (GBOS) représentée par Monsieur Allé Nar DIOP,
                Directeur général de ladite Institution, sise à Rocade Fann Bel-air Cerf-Volant, BP : 116 Dakar RP, Dakar, ayant donné délégation de signature à Monsieur ................., ………………………………,
                Chef du Service Régional <br>de la Statistique et de la Démographie de la région de $region, 
                ci-après désigné « Employeur » ;
                <br><br><strong>ET,</strong> 
                <br><br>Mr/Mme/Mlle <strong>$prenom $nom</strong> né (e) le <strong>$datenaiss</strong> à <strong>$lieunaiss</strong>, N° CNI <strong>$cni</strong> du <strong>date_déliv</strong>, Domicilié (e) à <strong>$adresse</strong>.
                <br> <br>II a été convenu ce qui suit :
            </p>
            <p><strong><u>Article 1:</u></strong> 
                <br>Mr/Mme/Mlle <strong>$prenom $nom</strong> est recruté (e) pour exercer les prestations de services relatives aux tâches <strong>d'agent recenseur</strong> dans le cadre du <strong> 5<sup>e</sup> Recensement général de la Population et de l’Habitat (RGPH-5)</strong>.
            </p>
            <p><strong><u>Article 2:</u></strong> 
                <br>Le prestataire est recruté pour exécuter les activités qui incombent à un agent recenseur dans le cadre du RGPH-5, Cf. TDR de recrutement des AR. L’agent recenseur est un agent de terrain qui peut être redéployé à tout moment en cas de nécessité impérieuse. Il travaille sous la tutelle directe du contrôleur et sous la responsabilité du superviseur. Il (Elle) est appelé (e) à intervenir sur l’ensemble du territoire national.                 
                <br>Le prestataire peut être appelé aussi à exercer ses prestations de service au-delà des heures normales de travail et pendant les jours fériés, en cas de besoin, compte tenu de la spécificité de l’opération. Il n’a droit qu’à un (1) jour de congé par semaine. Face à une situation exceptionnelle (fêtes ou événements religieux) ou en cas de force majeur (décès d’un parent, maladie), le prestataire est tenu de formuler une demande de permission qui sera soumise à la Coordination du RGPH-5.
                <br><br>Il (Elle) est soumis (e) aux mêmes devoirs et obligations que les agents de l’Agence dans l’exercice de leurs fonctions au respect strict du devoir de réserve et au secret professionnel.                         
            </p>
            <p><strong><u>Article 3:</u></strong> 
                <br>Le présent contrat est conclu pour une durée $numToLetter <b>($dureeContrat) $jrMonthYea</b>. II prend effet à compter de la date de prise de service de l’intéressé.
            </p>
            <p><strong><u>Article 4:</u></strong> 
                <br>Mme/Mlle/M. s’engage à restituer au terme du contrat à l’GBOS le matériel qui lui a été remis lors de son engagement. La non restitution entraine  la rétention du salaire en concurrence du matériel ou des accessoires mis à sa disposition et des poursuites judiciaires.
            </p>
            <p><strong><u>Article 5:</u></strong> 
                <br>Toute démission ou arrêt de prestation doit faire l’objet d’un préavis écrit d’au moins deux semaines. A défaut, le contrat est rompu avec retenue d’indemnité de prestation de services équivalente à la période de préavis non respectée.
            </p>
            <p><strong><u>Article 6:</u></strong> 
                <br>En cas d’insatisfaction ou de fautes lourdes, l’GBOS se réserve le droit de résilier sans préavis ou de ne pas renouveler le contrat.
            </p>
            <p><strong><u>Article 7:</u></strong> 
                <br>En cas d’insatisfaction ou de fautes lourdes, l’GBOS se réserve le droit de résilier sans préavis ou de ne pas renouveler le contrat.
            </p>
            <p><b><u>Article 8:</u></b> 
                <br>Le montant brut des prestations de Mr/Mlle/Mme <strong>$prenom $nom</strong> pour $numToLetterReal ($dureeContrat) $jrMonthYea de travail est fixé à <strong>$salaireToLetter ($salaireAR)</strong> francs CFA. 
                <br>Ce montant inclut tous les frais associés à cette activité. 
                <br>Le règlement des sommes dues au titre des prestations ne sera effectif que sur présentation d’une attestation de service fait signée établie par le <b>Chef de service de la Statistique et de la Démographie</b>. <br>
                - L’Agence ne prend pas en charge les cotisations à l’IPRES et à la Caisse de Sécurité Sociale.  <br>
                - Le contrat prendra fin sans indemnités ni primes de rupture. <br>
                - L’Agence procédera à une retenue de 5% du montant brut des prestations conformément à la législation fiscale.           
            </p>
        </p>
        
        EOD;

        $pdf->writeHTML($html);
        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Fait à $region, le $df", true, false, false, false, "R");
        $pdf->writeHTML("Pour le Directeur général de l'GBOS et par délégation", true, false, false, false, "R");
        $pdf->writeHTML("Le Chef de service Régional de la Statistique et de la Démographie de ...", true, false, false, false, "R");
        $pdf->writeHTML("Paraphe", true, false, false, false, "R");
        $pdf->writeHTML("Cachet", true, false, false, false, "R");
        $pdf->writeHTML("Prénoms et Nom", true, false, false, false, "R");
        // $pdf->writeHTML("<br/><br/>", true, false, false, false, "C");
        $pdf->writeHTML("Le prestataire <br> ‘‘Lu et approuvé’’<br><br>", true, false, false, false, "");
        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Cachet + Signature", true, false, false, false, "R");
        $pdf->writeHTML("Prénom Nom du Chef de service", true, false, false, false, "R");

        try {
            //code...
            $dstFile = $kernel->getProjectDir() . '/var/csdbs/contrat_' . $acteur->getDepartement()->getCode();

            $filename = $acteur->getId() . '_CONTRAT_AR_' . $acteur->getDepartement() . "_" . $prenom . '_' . $nom . '.pdf';
            $fileNL = $dstFile . "/" . $filename; //Linux

            $pdf->Output($fileNL, 'F');
        } catch (\Exception $th) {
            //throw $th;
        }
    }

    public static function modelContratControleur(
        $kernel,
        $acteur,
        ApplicationsRepository $applicationsRepository,
        ContratParamRepository $repo,
        SalaireBaseRepository $salaireBaseRepository
    ) {
        $region = $acteur->getDepartement()->getRegion()->getSurname();

        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        $prenom = !empty($acteur->getName()) ? strtoupper($acteur->getName()) : "";
        $nom = !empty($acteur->getSurname()) ? strtoupper($acteur->getSurname()) : "";

        $cni = $acteur->getCni();
        $datenaiss = $candidat->getDateNaissance()->format("d/m/Y");
        $lieunaiss = $candidat->getLieuNaissance();
        $adresse = $acteur->getAdresse();

        // informations globales Contrat
        $contratParam = $repo->findAll()[0];
        $dureeContrat = $contratParam->getDureeCollecte();
        $jrMonthYea = $dureeContrat == 1 ? $contratParam->getJrMonthYear() : $contratParam->getJrMonthYear() . "s";
        // 2 => deux, 5 => cinq
        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        $numToLetter = $dureeContrat == 1 ? "d'un" : "de " . $f->format($dureeContrat);
        $numToLetterReal = $f->format($dureeContrat);

        // salaire de base
        $salaireCE = 0;
        $salaires = $salaireBaseRepository->findAll();
        $salaireToLetter = "";

        foreach ($salaires as $sal) {
            if ($sal->getProfil() == "ROLE_CE") {
                $sa = floatval(trim((string) $sal->getMontant()));
                $salaireCE = number_format($sa, 0, '.', ' ');
                $salaireToLetter = $f->format($sal->getMontant());
            }
        }

        $pdf = new ContratPdf($kernel, 'CONTRAT DE PRESTATION DE SERVICES');
        $html =  <<<EOD
        <p class="c19">
            <p><strong>Le présent contrat est conclu : </strong> 
                <br><br><strong>ENTRE,</strong> 
                <br><br>L’Agence nationale de la Statistique et de la Démographie (GBOS) représentée par Monsieur
                    Allé Nar DIOP, Directeur général de ladite Institution, sise à Rocade Fann Bel-air Cerf-Volant,
                    BP : 116 Dakar RP, Dakar, ayant donné délégation de signature à Monsieur ................., ………………………………,
                    Chef du Service Régional <br>de la Statistique et de la Démographie de la région de $region, ci-après désigné « Employeur » ;
                <br><br><strong>ET,</strong> 
                <br><br>Mr/Mme/Mlle <strong>$prenom $nom</strong> né (e) le <strong>$datenaiss</strong> à <strong>$lieunaiss</strong>, N° CNI <strong>$cni</strong> du <strong>date_déliv</strong>,
                Domicilié (e) à <strong>$adresse</strong>.
                <br> <br>II a été convenu ce qui suit :
            </p>
            <p><strong><u>Article 1:</u></strong> 
                <br>Mr/Mme/Mlle <strong>$prenom $nom</strong> est recruté (e) pour exercer les prestations de services relatives aux tâches de <strong>Contrôleur</strong> dans le cadre du <strong> 5<sup>e</sup> Recensement général de la Population et de l’Habitat (RGPH-5)</strong>.
            </p>
            <p><strong><u>Article 2:</u></strong> 
                <br>Le prestataire est recruté pour exécuter les activités qui incombent à un Contrôleur dans le cadre du RGPH-5, Cf. TDR de recrutement des Contrôleurs. Le contrôleur peut être sollicité pour appuyer dans les travaux effectués durant la période du dénombrement. Il travaille sous la tutelle directe du superviseur, sous l’encadrement technique du Coordonnateur technique départemental (CTD) et sous la responsabilité administrative du Chef SRSD. Il (Elle) est appelé (e) à intervenir sur l’ensemble du territoire national.
                <br><br>Le contrôleur peut être appelé à exercer ses prestations de service au-delà des heures normales de travail et pendant les jours fériés, en cas de besoin, compte tenu de la spécificité de l’opération. Il n’a droit qu’à un (1) jour de congé par semaine. Face à une situation exceptionnelle (fêtes ou événements religieux) ou en cas de force majeur (décès d’un parent, maladie), le prestataire est tenu de formuler une demande de permission qui sera soumise à la coordination du RGPH-5.
                <br>Il (Elle) est soumis (e) aux mêmes devoirs et obligations que les agents de l’Agence dans l’exercice de leurs fonctions au respect strict du devoir de réserve et au secret professionnel.
            </p>
            <p><strong><u>Article 3:</u></strong> <br>Le présent contrat est conclu pour une durée $numToLetter <b>($dureeContrat) $jrMonthYea</b>. II prend effet à compter de la date de prise de service de l’intéressé.
            </p>
            <p><strong><u>Article 4:</u></strong> 
                <br>Mme/Mlle/M. s’engage à restituer au terme du contrat à l’GBOS le matériel qui lui a été remis lors de son engagement. La non restitution entraine  la rétention du salaire en concurrence du matériel ou des accessoires mis à sa disposition et des poursuites judiciaires.
            </p>
            <p><strong><u>Article 5:</u></strong> 
                <br>Toute démission ou arrêt de prestation doit faire l’objet d’un préavis écrit d’au moins deux semaines. A défaut, le contrat est rompu avec retenue d’indemnité de prestation de services équivalente à la période de préavis non respectée.
            </p>
            <p><strong><u>Article 6:</u></strong> 
                <br>En cas d’insatisfaction ou de fautes lourdes, l’GBOS se réserve le droit de résilier sans préavis ou de ne pas renouveler le contrat.
            </p>
            <p><strong><u>Article 7:</u></strong> 
                <br>Le montant brut des prestations de Mr/Mlle/Mme <strong>$prenom $nom</strong> pour $numToLetterReal ($dureeContrat) $jrMonthYea de travail est fixé à <strong>$salaireToLetter ($salaireCE)</strong> francs CFA. 
                <br>Ce montant inclut tous les frais associés à cette activité. 
                <br>Le règlement des sommes dues au titre des prestations ne sera effectif que sur présentation d’une attestation de service fait signée établie par le <b>Chef de service de la Statistique et de la Démographie</b>. <br>
                - L’Agence ne prend pas en charge les cotisations à l’IPRES et à la Caisse de Sécurité Sociale.  <br>
                - Le contrat prendra fin sans indemnités ni primes de rupture. <br>
                - L’Agence procédera à une retenue de 5% du montant brut des prestations conformément à la législation fiscale.           
            </p>
        
        EOD;

        $pdf->writeHTML($html);

        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Fait à $region, le $df", true, false, false, false, "R");
        $pdf->writeHTML("Pour le Directeur général de l'GBOS et par délégation", true, false, false, false, "R");
        $pdf->writeHTML("Le Chef de service Régional de la Statistique et de la Démographie de ...", true, false, false, false, "R");
        $pdf->writeHTML("Paraphe<br><br>", true, false, false, false, "R");
        $pdf->writeHTML("Cachet<br><br>", true, false, false, false, "R");
        $pdf->writeHTML("Prénoms et Nom<br><br>", true, false, false, false, "R");
        // $pdf->writeHTML("<br/><br/>", true, false, false, false, "C");
        $pdf->writeHTML("Le prestataire &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ‘‘Lu et approuvé’’<br><br>", true, false, false, false, "");

        try {
            //code...
            $dstFile = $kernel->getProjectDir() . '/var/csdbs/contrat_' . $acteur->getDepartement()->getCode();
            // if (!file_exists(dirname($dstFile))) {
            //     $filesystem = new Filesystem();
            //     $filesystem->mkdir($dstFile, 0777);
            //     // mkdir(dirname($dstFile), 0777, true);
            // }
            $filename = $acteur->getId() . '_CONTRAT_CONTROLEUR_' . $acteur->getDepartement() . "_" . $prenom . '_' . $nom . '.pdf';
            $fileNL = $dstFile . "/" . $filename; //Linux

            $pdf->Output($fileNL, 'F');
        } catch (\Exception $th) {
            //throw $th;
        }
    }

    public static function attestationsGroupees(
        string $nomDept,
        KernelInterface $kernel,
        CommunesArrCommunautesRurales $cacr,
        ContratParamRepository $repo,
        UserRepository $userRepo,
        SallesRepository $sallesRepo,
        CompositionRepository $compositionRepository,
        ApplicationsRepository $applicationsRepository,
        int $id=null
    ) {
        // $departement = $acteur->getDepartement()->getSurname();
        $csrsd = $userRepo->findOneBy(['id' => $id]);

        $region = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getRegion()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getDepartement()->getRegion()->getSurname();
        $departement = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getDepartement()->getSurname();
        $arrond = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getSurname();
        $cacrNam = $cacr->getSurname();

        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        // $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        $prenom = strtoupper($csrsd->getSurname());
        $nom = strtoupper($csrsd->getSurname());
        $cni = "#############";
        $datenaiss = "12/12/2022";

        // informations globales Contrat
        $contratParam = $repo->findAll()[0];
        $dureeContrat = $contratParam->getDureeCollecte();
        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);

        // // salaire de base
        // $salaires = $salaireBaseRepository->findAll();

        // if (count($salaires) > 0) {
        //     $sa = floatval(trim((string)$salaires[0]->getMontant()));
        // }

        $pdf = new ContratPdf($kernel, 'ATTESTATION DE PRISE DE SERVICE');
        $html = <<<EOD
        <style> p {font-size: 14px; font-style: normal !important;}</style>
        <p class="c19">
            <p>Je soussigné(e), Monsieur/Madame $prenom $nom, <b>le chef du Service régional de la Statistique et de la Démographie de $region</b>, atteste que les <b>Agents Recenseurs</b> dont les 
                <br/>noms suivent (tableau en annexe), recrutés dans le cadre du projet RGPH-5, 
                <br/>ont pris service le 15 mai 2023
                <br/><br/>La présente attestation est délivrée pour servir et valoir ce que de droit. 
            </p>
        EOD;

        $pdf->writeHTML($html);

        $pdf->SetRightMargin(20);
        $html =  <<<EOD
            <style> p {font-size: 16px; font-style: normal !important;}</style>
            <p class="c19"><br></<p>
        EOD;

        $pdf->writeHTML($html);
        $pdf->SetFont( '','',12 );
        $pdf->writeHTML("Fait à $region, le $df <br>
                         Pour le Directeur général <br>de l'GBOS
                         et par délégation <br>
                         Le Chef de service Régional de la <br> 
                         Statistique et de la Démographie de

                        ", true, false, false, false, "R");
        
        $pdf->writeHTML("<b>$prenom $nom </b> <br>", true, false, false, false, "R");
          
        $html =  <<<EOD
            <style> p {font-size: 16px; font-style: normal !important;}</style>
            <p class="c12">
                Le prestataire &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ‘‘Lu et approuvé’’
            </<p>
        EOD;

        $pdf->writeHTML($html);
        $pdf->AddPage();

        $html =  <<<EOD
            <style> p {font-size: 14px; font-style: normal !important;}</style>
            <p class="c19">
                Les agents recenseurs sont répartis en fonction du découpage administratif suivant :
                    <br>
                    Région : ………………………… $region ……………………………….. 
                    <br>
                    Département : …………… $departement ……………………
                    <br>
                    Arrondissement :……… $arrond …………………………..
                    <br>
                    Commune :…………………... $cacrNam ………………………………
                    <br>
            </<p>
        EOD;

        $pdf->writeHTML($html);

        // saut de Page
       // $pdf->AddPage();
        $pdf->writeHTML("<h3>TABLEAU ANNEXE : LISTE DES AGENTS RECENSEURS <br></h3>", true, false, true, false, '');

        // liste des agents
        $i = 0;
        $data = [];
        // $contractuels = $userRepo->findBy(["commune" => $cacr]);
        $contractuels = $compositionRepository->findSupCompositonCCRCA($cacr);
        // var_dump($contractuels); die;
        foreach ($contractuels as $u) {
            // $uInfo = $sallesRepo->findOneBy(["login" => $u->getArr()->getEmail()]);
            // if ($uInfo != null) {
            // if ($u->getArr()->getRoles()[0] != "ROLE_CE") {

            $i++;
            $ar = $u->getArr();

            $prenom = $ar->getName();
            $nom = strtoupper($ar->getSurname());
            $tel = $ar->getTelephone();
            $cni = $ar->getCni();
            $datep = $u->getPriseServiceAt();
            $datepf = $datep ? $datep->format('d-m-Y') : "";

            $arInfo = $applicationsRepository->findOneBy(["nin" => $cni]);

            if ($arInfo != null) {
                $datnaiss = $arInfo->getDateNaissance();
                $datef = $datnaiss->format('d-m-Y');
                $datenaiss = $datef . " à " . $arInfo->getLieuNaissance();
                $lieunaiss =  $arInfo->getLieuNaissance();
            }

            $data[] = [$i, $prenom, $nom, "Agent recenseur", $datepf];
            // }
        }

        // if (count($contractuels) > 0) {
        $headers = ["N°", "Prénom(s)", "Nom", "Fonction", "Date"];
        $pdf->SetLeftMargin(25);
        $pdf->addTable($headers, $data);

        try {
            $dstFile = $kernel->getProjectDir() . '/var/attestation_prise_de_service_groupee_' . $nomDept;
            $filename = 'PRISE_DE_SERVICE_GROUPEE_' . $cacr->getSurname() . "_" . $cacr->getCode() . '.pdf';
            $fileNL = $dstFile . "/" . $filename; //Linux

            $pdf->Output($fileNL, 'F');
        } catch (\Exception $th) {
            throw $th;
        }
        // }
    }

    // TODO: Contrat collectif | AGENTS RECENSEURS
    public static function contratCollectif(
        string $codeDept,
        KernelInterface $kernel,
        CommunesArrCommunautesRurales $cacr,
        ContratParamRepository $repo,
        SalaireBaseRepository $salaireBaseRepository,
        SallesRepository $sallesRepository,
        UserRepository $userRepository,
        CompositionRepository $compositionRepository,
        User $srsd,
        ApplicationsRepository $applicationsRepository,
        int $salaire,
        Departements $dept
    ) {
        $srsdName = $srsd->getName() . " " . $srsd->getSurname();
        $srsdAdresse = $srsd->getAdresse();

        // $departement = $acteur->getDepartement()->getSurname();
        $region = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getRegion()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getDepartement()->getRegion()->getSurname();

        // $departement = $cacr->getEstDirectementRattachDept()
        //     ? $cacr->getDepartement()->getSurname()
        //     : $cacr->getCommuneArrondissementVille()->getDepartement()->getSurname();

        $departement = $dept->getSurname();

        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        // $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        $prenom = strtoupper($cacr->getSurname());
        $nom = strtoupper($cacr->getSurname());

        // informations globales Contrat
        $contratParam = $repo->findAll()[0];
        $dureeContrat = $contratParam->getDureeCollecte();
        $jrMonthYea = $dureeContrat == 1 ? $contratParam->getJrMonthYear() : $contratParam->getJrMonthYear() . "s";

        $datePriseService = $contratParam->getDatepriseAt()->format('Y-m-d');
        $dateVal = date_create($datePriseService);
        $endDate = date_add($dateVal, date_interval_create_from_date_string("30 days"));
        $endPrise_f = $endDate->format('d-m-Y');

        // $datePriseService = $contratParam->getDatepriseAt()->format("d/m/Y");
        // $structureAcceuil = $contratParam->getAcceuiliAla();
        // $sousLaCoordination = $contratParam->getSousLaCoordination();
        // 2 => deux, 5 => cinq
        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        $numToLetter = $dureeContrat == 1 ? "d'un" : "de " . $f->format($dureeContrat);
        $numToLetterReal = $f->format($dureeContrat);

        // salaire de base
        $salaireCE = 0;
        $salaireToLetter = "";

        $salaireCE = number_format($salaire, 0, '.', ' ');
        $salaireToLetter = $f->format($salaire);

        $pdf = new ContratPdf($kernel, 'CONTRAT DE PRESTATION DE SERVICES');

        $regionName = str_pad($region, 50, ".", STR_PAD_BOTH);
        $departementName = str_pad($departement, 50, ".", STR_PAD_BOTH);
        $cavName = str_pad($cacr->getCommuneArrondissementVille()->getSurname(), 50, ".", STR_PAD_BOTH);
        $communeName = str_pad($cacr->getSurname(), 50, ".", STR_PAD_BOTH);

        $html =  <<<EOD
        <style> p {font-size: 14px; font-style: normal !important; line-height: 220%;}</style>
        <p>
            <p><b>ENTRE</b> <br/>
                L’Agence nationale de la Statistique et de la Démographie (GBOS) représentée par Monsieur Aboubacar Sédikh BEYE 
                , Directeur général de ladite Institution, sise à Rocade Fann Bel-air Cerf-Volant, BP : 116 Dakar RP, ayant donné délégation de signature à
                Monsieur/Madame <b>$srsdName</b>, Chef du service régional de la Statistique et de la Démographie de la région de $regionName, 
                ci-après désigné comme &laquo;Employeur&raquo;.
                <br/><strong>ET</strong> 
                <br/>Les agents recenseurs dont les informations figurent dans le tableau ci-dessous,
                repartis en fonction du découpage administratif suivant.<br/><br/><b>Région:</b> $regionName<br/><b>Département:</b> $departementName<br/><b>Arrondissement:</b> $cavName<br/><b>Commune:</b> $communeName
            </p>
        EOD;
        $pdf->writeHTML($html);

        $pdf->AddPage("L");
        // $pdf->writeHTML("<h3>Annexes <br></h3>", true, false, true, false, '');

        $superviseursDept = $userRepository->findUserByRolesInDepartement("SUPERVISEUR", $dept);
        $countRow=0; 

        $pageCreate = 2;
        foreach ($superviseursDept as $sup) {

            $j = 1;
            $i = 0;

            $contractuels = $compositionRepository->findSupCompositonCCRCA($cacr, $sup);

            if (count($contractuels) > 0) {
                $countRow++;
                $data = [];
                foreach ($contractuels as $ar) {
                    if ($ar->getArr()->getRoles()[0] != "ROLE_CE") {
                        $i++;
                        //
                        $nom = strtoupper($ar->getArr()->getSurname());
                        $prenom = $ar->getArr()->getName();
                        $tel = $ar->getArr()->getTelephone();
                        $cni = $ar->getArr()->getCni();
                        $datenaiss = "";

                        $arInfo = $applicationsRepository->findOneBy(["nin" => $ar->getArr()->getCni()]);

                        if ($arInfo != null) {
                            $datnaiss = $arInfo->getDateNaissance();
                            $datef = $datnaiss->format('d-m-Y');
                            $datenaiss = $datef . " à " . $arInfo->getLieuNaissance();
                        }

                        $data[] = [$i, '<span style="text-align: left;">' . $prenom . " " . $nom . '</span>', $datenaiss, $cni, $tel, "<br><br>"];
                    }
                }

                $headers = ["N°", "Prénom(s) nom", "Date et lieu de naissance", "N° Carte d'Identité", "N° Téléphone", "Signature",];
                $pdf->addTable($headers, $data,  NULL, ucfirst($sup->getName()) . ' ' . strtoupper($sup->getSurname()) . ' | ' . $sup->getEmail());

                $pdf->AddPage();
                $pageCreate++;

            }
        }

        $pdf->AddPage("P");

        $pdf->resetFooterText();

        $articleHtml =  <<<EOD
        <style> p {font-size: 14px; font-style: normal !important; line-height: 140%;}</style>
        <p>Il a été convenu ce qui suit:
            <p><strong><u>Article premier:</u></strong> 
                <br>Les concernés sont recrutés pour exercer les prestations de services relatives aux tâches <b>d’Agents recenseurs</b> dans le cadre <b>du 5<sup>e</sup> Recensement général de la Population et de l’Habitat (RGPH-5)</b>. Ils peuvent être affectés et redéployés en cas de nécessité à l’intérieur de leur commune  d’affectation, pendant toute la durée de l’opération.
            </p>
            <p><strong><u>Article 2:</u></strong> 
                <br>Les agents recenseurs ci-dessus ont donné leurs accords pour l’utilisation de leurs données à caractére personnel pour l’élaboration de ce présent contrat collectif dans le cadre du <b>RGPH-5</b>. 
                <br><br>Les contrats d'adhésion font partie intégrante de ce contrat de prestation de services.
            </p>
            <p><strong><u>Article 3:</u></strong> 
                <br>L’Agent recenseur est mis à la disposition du Chef de Service régional de la Statistique et de la Démographie, à l’issue de l’évaluation post formation des candidats. Ils sont sous la responsabilité administrative du chef de Service régional de la Statistique et de la Démographie, sous l’encadrement du Coordonnateur technique régional/départemental et sous la supervision directe d’un superviseur et d'un contrôleur.<br/><br/>
                L' agent recenseur aura à exécuter les activités suivantes :
                <ul>
                    <li>prendre contact avec le chef de village ou le chef/délégué de quartier pour lui faire part des objectifs du recensement;</li>
                    <li>faire la reconnaissance de son District de Recensement (DR) pour délimiter ses contours, afin d’éviter les chevauchements et les doubles-comptes ;</li>
                    <li>concrétiser son DR en actualisant les listes des concessions et des ménages issus de la cartographie (pour chaque concession, actualiser la liste des ménages) ;</li>
                    <li>confronter la liste des concessions et des ménages concrétisés avec la liste des concessions et des ménages issue de la cartographie et au besoin alerter le contrôleur ; </li>
                    <li>remonter toutes les données issues de la concrétisation selon le schéma de transmission retenu;</li>
                    <li>dénombrer tous les ménages dans les concessions ou infrastructures cartographiées et concrétisés de son DR ;</li>
                    <li>suivre le planning de déplacement élaborer avec le contrôleur ;</li>
                    <li>collecter les caractéristiques de tous les individus du ménage et des unités d’habitation de son DR ;</li>
                    <li>procéder à la synchronisation par Bluetooth pour remonter les données collectées auprès des ménages vers les superviseurs ;</li>
                    <li>faire des sauvegardes des données collectées directement vers le serveur avec sa tablette à l’aide d’une connexion Internet;</li>
                    <li>faire des backups sur la carte SD embarquée dans la tablette ;</li>
                    <li>collecter les données sur les concessions affectées par le chef d’équipe en cas de ratissage ou d’appui d’un autre AR; procéder à la synchronisation par Bluetooth pour la transmission directe des données collectées sur le terrain avec sa tablette vers le contrôleur pour le contrôle d’exhaustivité et de qualité des données lors des contre-enquêtes.</li>
                </ul>
                <br>
                <strong><u>Article 4:</u></strong> 
                <br>Le présent contrat est conclu pour une durée de un (1) mois. 
                Les agents recenseurs bénéficiaires de ce présent contrat sont soumis aux mêmes  obligations de réserve et au secret professionnel que les agents de l’GBOS. <br/>
                L’agent recenseur peut être appelé, dans le cadre des opérations de dénombrement, à exercer leur prestation de services  au-delà des heures normales de travail. <br/>
                L’agent recenseur est tenu de travailler pendant les jours fériés compte tenu de la spécificité de l’opération.
            </p>
            <p><strong><u>Article 5:</u></strong> 
                <br>L’arrêt de prestation de l’agent recenseur sans autorisation préalable formelle par voie hiérarchique, constituent une faute grave. Il peut faire l’objet d’une rupture du contrat sans aucune indemnité.
            </p>
            <p><strong><u>Article 6:</u></strong> 
             <br>L’agent recenseur doit être libre de tout engagement durant toute la période du dénombrement.
             <br>L’agent recenseur s’engage à accomplir convenablement les tâches qui lui sont confiées par l’GBOS dans le cadre de ce RGPH-5.
             <br>Toute tentative de sabotage, de boycott ou d’incitation au sabotage ou au boycott, expose l’agent recenseur à des poursuites judiciaires.
             </p>
            <p><strong><u>Article 7:</u></strong> 
                <br>L’agent recenseur doit restituer tout le matériel remis par l’Agence en l’état dès l’arrêt de la prestation. En cas de non-respect de cette disposition, l’Agence se réserve le droit par les moyens légaux en vigueur, de récupérer son matériel, de se faire dédommager et d’engager toute poursuite à l’encontre de l’agent recenseur pour la sauvegarde de ses intérêts, conformément à la note de service N°00002294/MEPC/GBOS/DSID/DI du 11 mai 2023.
             </p>
            <p><strong><u>Article 8:</u></strong> 
                <br>Le montant brut des prestations d’un agent recenseur pour un (01) mois de travail est fixé à cent cinquante-sept mille huit cent quatre-vingt-quinze (157 895) francs CFA. 
                <br><br>Le paiement est fait sur la base d’une <b>attestation de prise de service et de service fait</b> établie et signée par le Chef du Service régional de la Statistique et de la Démographie et d’un <b>quitus matériel</b> délivré par un Assistant en Technologie de l’information et de la Communication (ATIC), sous la supervision des Coordonnateurs administratifs départementaux (CAD).
                <ul>
                    <li>L’Agence ne prend pas en charge les cotisations à l’IPRES et à la Caisse de Sécurité Sociale.</li> 
                    <li>Le contrat prendra fin sans indemnités ni primes de rupture</li>
                    <li>L’Agence procédera à une retenue à la source de 5% du montant brut des prestations, conformément à la législation fiscale.</li>          
                    <li>Après retenue à la source, le montant net à percevoir par l’agent recenseur est de cent cinquante mille  (150 000) francs CFA</li><br/> 
                    <b>Le paiement se fera par mobile money (Wave ou Orange Money).</li>
                </ul>
            </p>
        </p>
        
        EOD;

        $pdf->writeHTML($articleHtml);

        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Fait à $region, le $df <br/>", true, false, false, false, "R");
        $pdf->writeHTML("<b style='margin-left: 80'>Pour le Directeur général de l’GBOS
        et par délégation
        Le Chef du Service régional de la
        Statistique et de la Démographie</b>", true, false, false, false, "R");

        // $pdf->deletePage($pageCreate);


        try {
            if ($countRow > 0) {
                $dstFile = $kernel->getProjectDir() . '/var/contrat_collectif_' . $codeDept;

                $filename = 'CONTRAT_COLLECTIF_AGENTS_RECENSEURS_' . $cacr->getSurname() . "_" . $cacr->getCode() . '.pdf';
                $fileNL = $dstFile . "/" . $filename; //Linux

                $pdf->Output($fileNL, 'F');
            }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    // TODO: Contart collectif | Controleurs
    public static function genericContratControleurByCacr(
        string $codeDept,
        KernelInterface $kernel,
        CommunesArrCommunautesRurales $cacr,
        ContratParamRepository $repo,
        CompositionRepository $compositionRepository,
        ApplicationsRepository $applicationsRepository,
        User $srsd,
        int $salaire,
        UserRepository $userRepository
    ) {
        // $departement = $acteur->getDepartement()->getSurname();
        $srsdName = $srsd->getName() . " " . $srsd->getSurname();
        $srsdAdresse = $srsd->getAdresse();

        $region = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getRegion()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getDepartement()->getRegion()->getSurname();

        $departement = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getDepartement()->getSurname();


        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        // $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        $prenom = strtoupper($cacr->getSurname());
        $nom = strtoupper($cacr->getSurname());

        // informations globales Contrat
        $contratParam = $repo->findAll()[0];
        $dureeContrat = $contratParam->getDureeCollecte();
        $jrMonthYea = $dureeContrat == 1 ? $contratParam->getJrMonthYear() : $contratParam->getJrMonthYear() . "s";

        $datePriseService = $contratParam->getDatepriseAt()->format('Y-m-d');
        $dateVal = date_create($datePriseService);
        $endDate = date_add($dateVal, date_interval_create_from_date_string("30 days"));
        $endPrise_f = $endDate->format('d-m-Y');
        // $datePriseService = $contratParam->getDatepriseAt()->format("d/m/Y");
        // $structureAcceuil = $contratParam->getAcceuiliAla();
        // $sousLaCoordination = $contratParam->getSousLaCoordination();
        // 2 => deux, 5 => cinq
        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        $numToLetter = $dureeContrat == 1 ? "d'un" : "de " . $f->format($dureeContrat);
        $numToLetterReal = $f->format($dureeContrat);

        // salaire de base
        $salaireToLetter = "";

        $salaireCE = number_format($salaire, 0, '.', ' ');
        $salaireToLetter = $f->format($salaire);

        $pdf = new ContratPdf($kernel, 'CONTRAT DE PRESTATION DE SERVICES');

        $regionName = str_pad($region, 50, ".", STR_PAD_BOTH);
        $departementName = str_pad($departement, 50, ".", STR_PAD_BOTH);
        $cavName = str_pad($cacr->getCommuneArrondissementVille()->getSurname(), 50, ".", STR_PAD_BOTH);
        $communeName = str_pad($cacr->getSurname(), 50, ".", STR_PAD_BOTH);

        $html =  <<<EOD
        <style> p {font-size: 14px; font-style: normal !important; line-height: 220%;}</style>
        <p>
            <p><b>ENTRE</b> <br/>
                L’Agence nationale de la Statistique et de la Démographie (GBOS) représentée par Monsieur Aboubacar Sédikh BEYE 
                , Directeur général de ladite Institution, sise à Rocade Fann Bel-air Cerf-Volant, BP : 116 Dakar RP, ayant donné délégation de signature à
                Monsieur/Madame <b>$srsdName</b>, Chef du service régional de la Statistique et de la Démographie de la région de $regionName, 
                ci-après désigné comme &laquo;Employeur&raquo;.
                <br/><strong>ET</strong> 
                <br/>Les contrôleurs dont les informations figurent dans le tableau ci-dessous,
                sont répartis en fonction du découpage administratif suivant:<br/><br/><b>Région:</b> $regionName<br/><b>Département:</b> $departementName<br/><b>Arrondissement:</b> $cavName<br/><b>Commune:</b> $communeName
            </p>
        EOD;
        $pdf->writeHTML($html);

        $pdf->AddPage("L");

        $superviseursDept = $userRepository->findUserByRolesInDepartement("SUPERVISEUR", $srsd->getDepartement());
        $countRow = 0;

        $pageAdd = 2;

        foreach ($superviseursDept as $sup) {
            $i = 0;

            $contractuels = $compositionRepository->findSupCompositonCCRCA($cacr, $sup, "ROLE_CE");

            if (count($contractuels) > 0) {
                $data = [];
                foreach ($contractuels as $ar) {
                    $countRow++;
                    $i++;
                    //
                    $nom = strtoupper($ar->getArr()->getSurname());
                    $prenom = $ar->getArr()->getName();
                    $tel = $ar->getArr()->getTelephone();
                    $cni = $ar->getArr()->getCni();
                    $login = $ar->getArr()->getEmail();
                    $datenaiss = "";

                    $arInfo = $applicationsRepository->findOneBy(["nin" => $ar->getArr()->getCni()]);
                    if ($arInfo != null) {
                        $datnaiss = $arInfo->getDateNaissance();
                        $datef = $datnaiss->format('d-m-Y');
                        $datenaiss = $datef . " à " . $arInfo->getLieuNaissance();
                    }

                    $data[] = [$i, '<span style="text-align: left;">' . $prenom . " " . $nom . '</span>', $datenaiss, $cni, $tel, "<br/><br/>"];
                }
                $headers = ["N°", "Prénom(s) nom", "Date et lieu de naissance", "N° Carte d'Identité", "N° Téléphone", "Signature"];
                $pdf->addTable($headers, $data,  NULL, ucfirst($sup->getName()) . ' ' . strtoupper($sup->getSurname()) . ' | ' . $sup->getEmail());

                $pdf->AddPage("L");
                $pageAdd++;
            }
        }

        $pdf->resetFooterText();

        $articleHtml =  <<<EOD
        <style> p {font-size: 14px; font-style: normal !important; line-height: 140%;}</style>
        <p>Il a été convenu ce qui suit:
            <p><strong><u>Article premier:</u></strong> 
             <br>Les concernés sont recrutés pour exercer les prestations de services relatives aux tâches <b>de contrôleur</b> dans le cadre <b>du 5<sup>e</sup> Recensement général de la Population et de l’Habitat (RGPH-5)</b>. Ils peuvent être affectés et redéployés en cas de nécessité à l’intérieur de leur commune  d’affectation, pendant toute la durée de l’opération.
            </p>
            <p><strong><u>Article 2:</u></strong> 
                <br>Les contrôleurs ci-dessus ont donné leurs accords pour l’utilisation de leurs données à caractére personnel pour l’élaboration de ce présent contrat collectif dans le cadre <b>RGPH-5</b>.
                <br/><br/>Les contrats d'adhésion font partie intégrante de ce contrat de prestation de services.
            </p>
            <p><strong><u>Article 3:</u></strong> 
                <br>Le Contrôleur est mis à la disposition du chef de Service régional de la Statistique et de la Démographie, à l’issue de l’évaluation post formation des candidats .Il est sous la responsabilité administrative du chef de Service régional de la Statistique et de la Démographie, sous l’encadrement du Coordonnateur technique régional/départemental et sous la supervision directe d’un superviseur.
                <br><br>Le contrôleur aura à exécuter les activités suivantes :
                <ul>
                    <li>sensibiliser les autorités et les populations;</li>
                    <li>installer les agents recenseurs dans le District de Recensement (DR);</li>
                    <li>gérer le matériel de terrain;</li>
                    <li>faire la reconnaissance de la zone de contrôle (ZC) et des DR ;</li>
                    <li>établir un planning de déplacement pour les agents recenseurs (AR) ;</li>
                    <li>assurer le tracking de la concrétisation des DR fait par les AR;</li>
                    <li>identifier des zones de transhumances et des zones de résidences des populations flottantes ;</li>
                    <li>transmettre les zones spécifiques aux superviseurs ;</li>
                    <li>organiser des appuis dans certains ménages collectifs ou difficiles ; </li>
                    <li>observer et vérifier le travail de l’agent recenseur ; </li>
                    <li>contrôler la qualité des données collectées ; </li>
                    <li>contrôler la couverture sur le terrain ;</li>
                    <li>dénombrer à nouveau des ménages déjà recensés par l’AR (au minimum 10 ménages) pour s’assurer de la qualité des données ; </li>
                    <li>dresser un bilan de la collecte (tableau récapitulatif, etc.) dans sa ZC ;</li>
                    <li>aider les ATIC à vérifier tout le matériel informatique, y compris les accessoires (chargeurs, power bank, etc.), avant, pendant et après le dénombrement.</li>
                </ul>
                <strong><u>Article 4:</u></strong> 
                     <br>Le présent contrat est conclu pour une durée d’un (1) mois. II prend effet à compter de la date de prise de service
                     <br><br>Les contrôleurs bénéficiaires de ce présent contrat sont soumis aux mêmes  obligations de réserve et au secret professionnel que les agents de l’GBOS
                     <br><br>Le contrôleur peut être appelé, dans le cadre des opérations de dénombrement, à exercer leur prestation de services  au-delà des heures normales de travail.
                     <br><br>Le contrôleur est tenu de travailler pendant les jours fériés compte tenu de la spécificité de l’opération.
                </p>
            <p><strong><u>Article 5:</u></strong> 
                <br>L’arrêt de prestation du contrôleur sans autorisation préalable formelle par voie hiérarchique, constitue une faute grave. Il peut faire l’objet d’une rupture du contrat sans aucune indemnité
             </p>
            <p><strong><u>Article 6:</u></strong> 
            <br><br>Le contrôleur doit être libre de tout engagement durant toute la période du dénombrement.
            <br><br>Le contrôleur s’engage à accomplir convenablement les tâches qui lui sont confiées par l’GBOS dans le cadre de ce RGPH-5. 
            <br><br>Toute tentative de sabotage, de boycott ou d’incitation au sabotage ou au boycott, expose le contrôleur à des poursuites judiciaires. 
            
            </p>
            <p><strong><u>Article 7:</u></strong> 
                <br>Le Contrôleur doit restituer tout le matériel remis par l’Agence en l’état, dès l’arrêt de la prestation. En cas de non-respect de cette disposition, l’Agence se réserve le droit par les moyens légaux en vigueur, de récupérer son matériel, de se faire dédommager et d’engager toute poursuite à l’encontre du contrôleur pour la sauvegarde de ses intérêts, conformément à la note de service N°00002294/MEPC/GBOS/DSID/DI du 11 mai 2023.
             </p>
            <p><strong><u>Article 8:</u></strong> 
            <br>Le montant brut des prestations d’un contrôleur pour un (01) mois de travail est fixé à cent quatre-vingt-quatre mille deux cent onze (184 211) francs CFA. 
            <br>Le paiement est fait sur la base d’une attestation de prise de service et de service fait établie et signée par le Chef du Service régional de la Statistique et de la Démographie et d’un quitus matériel délivré par un assistant en Technologie de l’information et de la Communication (ATIC), sous la supervision des Coordonnateurs administratifs départementaux (CAD).
             <ul>
                <li>L’Agence ne prend pas en charge les cotisations à l’IPRES et à la Caisse de Sécurité Sociale.  </li>
                <li>Le contrat prendra fin sans indemnités ni primes de rupture. </li>
                <li>L’Agence procédera à une retenue de 5% du montant brut des prestations conformément à la législation fiscale.  </li>
                <br><b>Le paiement se fera par mobile money (Wave ou Orange Money)</b> 
                </ul>       
            </p>
        </p>
        
        EOD;
        $pdf->writeHTML($articleHtml);

        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Fait à $region, le $df <br/>", true, false, false, false, "R");
        $pdf->writeHTML("<b>Le Chef SRSD de </b> $region", true, false, false, false, "R");

        $pdf->deletePage($pageAdd);

        try {
            if ($countRow > 0) {
                $dstFile = $kernel->getProjectDir() . '/var/contrat_collectif_' . $codeDept;

                $filename = 'CONTRAT_COLLECTIF_CONTROLEURS_' . $cacr->getSurname() . "_" . $cacr->getCode() . '.pdf';
                $fileNL = $dstFile . "/" . $filename; //Linux

                $pdf->Output($fileNL, 'F');
            }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public static function genericContratSuperviseursByDept(
        Departements $dept,
        KernelInterface $kernel,
        ContratParamRepository $repo,
        UserRepository $userRepository,
        User $srsd,
        int $salaire
    ) {
        $srsdName = $srsd->getName() . " " . $srsd->getSurname();
        $srsdAdresse = $srsd->getAdresse();

        $region = $dept->getRegion()->getSurname();

        $departement = $dept->getSurname();


        $dt = new DateTime();
        $df = $dt->format("d/m/Y");


        // informations globales Contrat
        $contratParam = $repo->findAll()[0];
        $dureeContrat = $contratParam->getDureeCollecte();
        $jrMonthYea = $dureeContrat == 1 ? $contratParam->getJrMonthYear() : $contratParam->getJrMonthYear() . "s";

        $datePriseService = $contratParam->getDatepriseAt()->format('Y-m-d');
        $dateVal = date_create($datePriseService);
        $endDate = date_add($dateVal, date_interval_create_from_date_string("60 days"));
        $endPrise_f = $endDate->format('d-m-Y');

        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        $numToLetter = $dureeContrat == 1 ? "d'un" : "de " . $f->format($dureeContrat);
        $numToLetterReal = $f->format($dureeContrat);

        // salaire de base
        $salaireToLetter = "";
        $salaireCE = number_format($salaire, 0, '.', ' ');
        $salaireToLetter = $f->format($salaire);

        $pdf = new ContratPdf($kernel, 'CONTRAT DE PRESTATION DE SERVICES');

        $regionName = str_pad($region, 50, ".", STR_PAD_BOTH);
        $departementName = str_pad($departement, 50, ".", STR_PAD_BOTH);
        $cavName = str_pad("", 50, ".", STR_PAD_BOTH);
        $communeName = str_pad("", 50, ".", STR_PAD_BOTH);

        $html =  <<<EOD
        <style> p {font-size: 14px; font-style: normal !important; line-height: 220%;}</style>
        <p class="c19">
            <p>Le présent contrat est conclu <b>ENTRE,</b> l’Agence nationale de la Statistique et de la Démographie (GBOS) représentée par Monsieur/Madame 
            <b>$srsdName</b>, Chef du service régional de la Statistique et de la Démographie (SRSD), sise à <b>$srsdAdresse</b>.
                <br/><strong>ET,</strong> 
                <br/>Les Superviseurs dont les informations figurent dans le tableau ci-dessous, repartis en fonction du découpage administratif suivant.<br/><br/><b>Région:</b> $regionName<br/><b>Département:</b> $departementName<br/>
            </p>
        EOD;
        $pdf->writeHTML($html);

        $pdf->AddPage();

        $i = 0;

        $contractuels = $userRepository->findUserByRolesInDepartement("SUPERVISEUR", $dept);

        $data = [];
        foreach ($contractuels as $sup) {
            $i++;
            //
            $nom = ucfirst($sup->getSurname());
            $prenom = ucfirst($sup->getName());
            $tel = $sup->getTelephone();
            $cni = $sup->getCni();

            $datenaiss = "";
            if ($sup->getDatenaiss() != NULL) {
                $datenaiss = $sup->getDatenaiss()->format('d/m/Y');
                $lieunaiss = ' à ' . $sup->getLieuNaiss();

                $datenaiss = $datenaiss . $lieunaiss;
            }

            $data[] = [$i, '<span style="text-align: left;">' . $prenom . " " . $nom . '</span>', '<span style="text-align: center;">' . $datenaiss . '</span>', $cni, $tel, "<br/><br/>"];
        }

        if (count($contractuels) > 0) {
            $headers = ["N°", "Prénom(s) nom", "Date et lieu de naissance", "N° Carte d'Identité", "N° Téléphone", "Signature"];
            $pdf->addTable($headers, $data,  "");
        } else {
            $pdf->writeHTML('<span><h4 style="text-align: center;">NEANT</h4></span>');
        }

        $pdf->AddPage();

        $articleHtml =  <<<EOD
        <style> p {font-size: 14px; font-style: normal !important; line-height: 140%;}</style>
        <p>Il a été convenu ce qui suit:
            <p><strong><u>Article premier:</u></strong> 
                <br>Les concernés sont recrutés pour exercer les prestations de services relatives aux tâches <b>de Superviseurs</b> dans le cadre <b>«  du 5<sup>e</sup> Recensement général de la Population et de l’Habitat (RGPH-5) »</b>. Ils peuvent être affectés dans toutes les régions et peuvent être redéployés en cas de nécessité à l’intérieur de leur région d’affectation ou dans une autre région au besoin, pendant toute la durée du contrat de prestation de services.
            </p>
            <p><strong><u>Article 2:</u></strong> 
                <br>Les Superviseurs ci-dessus ont donné leurs accords pour l’utilisation de leurs données à caractére personnel pour l’élaboration de ce présent contrat collectif dans le cadre <b>«  du 5<sup>e</sup> Recensement général de la Population et de l’Habitat (RGPH-5) »</b>.
            </p>
            <p><strong><u>Article 3:</u></strong> 
                <br><b>Les Superviseurs</b> seront accueillis et affectés au niveau des Services régionaux de la Statistique et de la Démographie et ils seront  sous la responsabilité du chef de service régional.<br/><br/>
                Ils ont des tâches, d’organisation, de formation, de gestion du personnel et surtout de contrôle de la collecte. <br><br/>
                <b>Tâches d'organisation:</b>
                <ul>
                    <li>prendre contact avec les autorités administratives, politiques, coutumières et religieuses, afin de les sensibiliser et solliciter leur assistance pour le bon déroulement des opérations ;</li>
                    <li>récupérer le matériel informatique auprès de l’ATIC sous la supervision du CAD ;</li>
                    <li>récupérer le matériel de terrain et les fournitures de bureau nécessaires auprès du CAD ;</li>
                    <li>procéder à la distribution du matériel et des fournitures de bureau nécessaires de concert avec l’Assistant TIC, pour le déroulement normal des opérations ;</li>
                    <li>réaffecter au besoin les tablettes aux agents recenseurs qui viennent en remplacement ou en appui, à partir de l’application du superviseur ;</li>
                    <li>identifier des zones de transhumances et des zones de résidences des populations flottantes ;</li>
                    <li>réaffecter les concessions d’un agent recenseur en retard sur le travail de collecte à d’autres agents recenseurs à partir de l’application du superviseur.</li>
                </ul>
                <b>Tâches de formation et d’évaluation des AR</b>
                <ul>
                    <li>faire valider par les AR à former les informations personnelles les concernant, avec l’application mobile de formation ;</li>
                    <li>envoyer les mises à jour effectuées sur les informations personnelles des AR, le cas échéant, au niveau central pour intégration dans la base de données des AR ;</li>
                    <li>distribuer le matériel de formation (questionnaire, manuel, calendrier historique, cahier, stylo) ;</li>
                    <li>affecter les tablettes reçues des ATIC sous la supervision du CAD, aux agents recenseurs durant leur formation ;</li>
                    <li>assurer la formation des agents recenseurs ;</li>
                    <li>évaluer les AR à la fin de la formation sur la base du corrigé de l’étude de cas fourni par la section conception ;</li>
                    <li>sélectionner les AR et les contrôleurs </li>
                    <li>former les contrôleurs sélectionnés </li>
                    <li>envoyer la liste des AR et des contrôleurs retenus après l’évaluation finale;</li>
                    <li>procéder à la récupération des données des DR à affecter aux AR ;</li>
                    <li>procéder à la composition des équipes ; </li>
                    <li>affecter les zones de contrôle aux contrôleurs et les DR aux AR ;</li>
                    <li>distribuer à chaque contrôleur le matériel et les équipements nécessaires pour le déroulement normal des opérations, en prenant soin de leur utilisation judicieuse ;</li>
                    <li>récupérer en collaboration avec les ATIC le matériel et les équipements affectés aux AR et contrôleurs.</li>
                </ul>
                <b>Tâche de gestion du Personnel</b>
                <ul>
                    <li>résoudre tous les problèmes administratifs (maladie, repos, indiscipline, etc.) ;</li>
                    <li>informer en cas de permutation, de démission ou de redéploiement d’AR ou de contrôleurs, le Coordonnateur technique départemental qui va informer à son tour le niveau régional, pour la mise à jour de la base des données du personnel de terrain ;</li>
                    <li>Soumettre les agents remplaçants et remplacés au CTD/CTR.</li>
                </ul>
                <b>Tâches de contrôle et de transfert des données</b>
                <ul>
                    <li>veiller à ce que les AR procèdent à la vérification de l’heure et de la date des tablettes pour éviter un calcul erroné de l’âge ;</li>
                    <li>assurer le tracking-évaluation de la collecte dans la zone de travail ;</li>
                    <li>procéder systématiquement à l’analyse des extraits de données collectées par les agents recenseurs pour informer la hiérarchie des manquements ou insuffisances observées;</li>
                    <li>récupérer tous les deux jours avec sa tablette les données collectées par les AR ;</li>
                    <li>vérifier l’exhaustivité et la qualité des données jusqu’au niveau ménage ;</li>
                    <li>voir les variables non renseignées et les messages d’alerte pour disposition à prendre ;</li>
                    <li>transmettre, après la concrétisation, les informations cartographiques mises à jour (nombre de concessions et de ménages) au niveau central en procédant à la remontée des données.</li>
                </ul>
                <strong><u>Article 4:</u></strong> 
                <br>Le présent contrat est conclu pour une durée de deux (2) mois. II prend effet à compter de la date de prise de service des intéressés (es) et finira le $endPrise_f.
                </br>Les Superviseurs signataires de ce présent contrat  sont soumis aux mêmes devoirs et obligations que les agents de l’Agence dans l’exercice de leurs fonctions, notamment le respect strict du devoir de réserve et du secret professionnel.
                <br/>Toutefois les superviseurs peuvent être appelés à exercer leur prestation de services partout où besoin sera, sur le territoire du pays, au-delà des heures normales de travail et pendant les jours fériés, en cas de besoin, compte tenu de la spécificité de l’opération.
            </p>
            <br/>
            <p><strong><u>Article 5:</u></strong> 
                <br>Toute démission ou arrêt de prestation doit faire l’objet d’un préavis écrit d’au moins deux semaines. A défaut, le contrat est rompu avec retenue d’indemnité de prestation de services équivalente à la période de préavis non respectée.
            </p>
            <p><strong><u>Article 6:</u></strong> 
                <br>Les arrêts de prestation des intéressés (es) sans autorisation formelle adressée au Chef du Service régional et aux Superviseurs  et validée par le Contrôleurs, constituent une faute grave. Ils peuvent faire l’objet d’une rupture du contrat sans aucune indemnité.
            </p>
            <p><strong><u>Article 7:</u></strong> 
                <br>En cas de désaccord entre les parties contractantes et en cas de litiges, les parties feront de leur mieux pour régler à l’amiable les différends.
            </p>
            <p><strong><u>Article 8:</u></strong> 
                <br>Le montant brut des frais de terrain d’un (01) Superviseur pour deux (02) mois de travail est fixé à <strong>$salaireToLetter ($salaireCE)</strong> francs CFA. 
                <br>Le paiement est fait sur la base d’une attestation de prise de service et service fait,  établie et signée par le Chef du Service régional de la Statistique et de la Démographie.
                <br>- L’Agence ne prend pas en charge les cotisations à l’IPRES et à la Caisse de Sécurité Sociale.  
                <br>- Le contrat prendra fin sans indemnités ni primes de rupture. 
                <br>- L’Agence procédera à une retenue de 5% du montant brut des prestations conformément à la législation fiscale.           
            </p>
        </p>
        
        EOD;
        $pdf->writeHTML($articleHtml);

        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Fait à $region, le $df <br/>", true, false, false, false, "R");
        $pdf->writeHTML("<b>Le Chef SRSD de </b> $region", true, false, false, false, "R");

        try {
            if (count($contractuels) > 0) {
                $dstFile = $kernel->getProjectDir() . '/var/contrat_collectif_' . $dept->getCode();

                $filename = 'CONTRAT_COLLECTIF_SUPERVISEURS_' . $dept->getSurname() . "_" . $dept->getCode() . '.pdf';
                $fileNL = $dstFile . "/" . $filename; //Linux

                $pdf->Output($fileNL, 'F');
            }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function footer($loginSUP = "")
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $numPage = $this->getAliasNumPage();
        $nbPages = $this->getAliasNbPages();

        $this->writeHTML('<table><tr><td style="width:30%"></td><td style="width:30%; text-align: center">' . $this->sup . '</td><td style="width:30%; text-align: right">' . $numPage . '/' . $nbPages . '</td></tr></table>', false, true, false, true);
    }


    public static function badgeCacrPrototype(
        SallesRepository $sallesRepository,
        KernelInterface $kernel,
        CommunesArrCommunautesRurales $cacr,
        UserRepository $repo,
        Departements $departement,
        string $srcFolder,
        CompositionRepository $compositionRepository,
        string $base_url
    ) {

        $dst = $kernel->getProjectDir() . "/public/qrcodes";
        if (!file_exists($dst)) {
            mkdir($dst, 0777, true);
        }

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set font
        $pdf->SetFont('times', '', 8);

        // supprime la ligne d'enetet en form <hr/>
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));

        // add a page
        $pdf->AddPage();

        // set cell padding
        $pdf->setCellPaddings(1, 1, 1, 1);

        // set cell margins
        $pdf->setCellMargins(1, 1, 1, 1);

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // ----------------

        // $contractuels = $repo->findBy(["commune" => $cacr], ['email' => 'ASC']);
        $contractuels = $compositionRepository->findSupCompositonCCRCA($cacr);

        $totalPage = 0;
        if (count($contractuels) > 0) {
            $sizeP = count($contractuels) / 4;

            // if (fmod($sizeP, 1) !== 0.00) {
            $totalPage  = intval($sizeP) + 1;
            // }

            if ($totalPage == 0) {
                $totalPage = 1;
            }
        }

        $region = $cacr->getEstDirectementRattachDept() != null
            ? $cacr->getDepartement()->getRegion()->getSurname() :
            $cacr->getCommuneArrondissementVille()->getDepartement()->getRegion()->getSurname();

        $pdf->SetLeftMargin(15);

        $mandatImg = '<img src="' . $kernel->getProjectDir() . '/public/dist/img/mandats.png' . '" width="160" height="70">';
        $logogphc5 = '&nbsp;&nbsp;&nbsp;<img src="' . $kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png' . '" width="60" height="60">';
        $logoGBOS = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . $kernel->getProjectDir() . '/public/dist/img/logo_grille.png' . '" width="60" height="60">';
        $bgImage = $kernel->getProjectDir() . '/public/dist/img/logo_GBOS.png';
        $slogan = '<img src="' . $kernel->getProjectDir() . '/public/dist/img/slogan.png' . '" width="250">';

        $pdf->SetAutoPageBreak(false);

        $superviseurs = [];
        $index = 0;
        for ($i = 0; $i < $totalPage; $i++) {
            $addingCount = 0;
            for ($k = 0; $k <= 3; $k++) {
                // test l'existence de l'index
                if (array_key_exists($index, $contractuels) == true) {
                    // $superviseurs[] = "SP" . substr($contractuels[$index]->getEmail(), 0, 4);

                    $addingCount++; // test si au moins 1 élement est ajouté pour créer l'autre Page

                    $nom = strtoupper($contractuels[$index]->getArr()->getSurname());
                    $prenom = $contractuels[$index]->getArr()->getName();
                    $login =  $contractuels[$index]->getLoginFormation();
                    $myRole =  $contractuels[$index]->getArr()->getRoles()[0];

                    // $nom = strtoupper($contractuels[$index]->getSurname());
                    // $prenom = $contractuels[$index]->getName();
                    // $login =  $contractuels[$index]->getEmail();
                    $profil = "";
                    $photoAgent = "";

                    // if ($contractuels[$index]->getRoles()[0] == "ROLE_AR") {
                    //     $profil = "Agent Recenseur";
                    // } elseif ($contractuels[$index]->getRoles()[0] == "ROLE_CE") {
                    //     $profil = "Chef d'équipe";
                    // } elseif ($contractuels[$index]->getRoles()[0] == "ROLE_AR_RATISSAGE") {
                    //     $profil = "AR ratissage";
                    // } elseif ($contractuels[$index]->getRoles()[0] == "ROLE_AR_PCP") {
                    //     $profil = "AR PCP";
                    // }

                    if ($myRole == "ROLE_AR") {
                        $profil = "Agent Recenseur";
                    } elseif ($myRole == "ROLE_CE") {
                        $profil = "Chef d'équipe";
                    } elseif ($myRole == "ROLE_AR_RATISSAGE") {
                        $profil = "AR ratissage";
                    } elseif ($myRole == "ROLE_AR_PCP") {
                        $profil = "AR PCP";
                    }

                    // try {
                    //     $candidatFormation = $sallesRepository->findCandidatByNIN($contractuels[$index]->getCni());
                    //     if ($candidatFormation) {
                    //         $filesystem = new Filesystem();
                    //         $oldPicture = $srcFolder . '/photo_' . $candidatFormation->getLogin() . '.jpg';
                    //         $newPicture = $srcFolder . '/photo_' . $login . '.jpg';

                    //         if (!file_exists($newPicture) && file_exists($oldPicture)) {
                    //             $filesystem->rename($oldPicture, $newPicture);
                    //         }

                    //         if (!file_exists($oldPicture) && file_exists($newPicture)) {
                    //             $filesystem->copy($newPicture, $oldPicture);
                    //         }
                    //     }
                    // } catch (\Throwable $th) {
                    //     //throw $th;
                    // }

                    // récupère la photo de l'agent
                    $dstFile = $srcFolder . "/photo_{$login}.jpg";
                    if (file_exists($dstFile)) {
                        $photoAgent = '<img class="myPicture" src="' . $srcFolder . '/photo_' . $login . '.jpg' . '" width="60" height="60" >';
                    } else {
                        $photoAgent = '<img class="myPicture" src="' . $kernel->getProjectDir() . '/public/dist/img/avatar.jpg' . '" width="60" height="60" >';
                    }

                    $qrcode = new \TCPDF2DBarcode($base_url . '/check_ar/' . $login, 'QRCODE,H');
                    $code =  $qrcode->getBarcodePngData();
                    $destination_folder = $kernel->getProjectDir() . "/public/qrcodes/barcode_$login.png";
                    file_put_contents($destination_folder, $code);
                    $qrcodeImg = '<img src="' . $destination_folder . '" width="50" height="50">';

                    $html = <<<EOD
                        <style> #bgImg { background-image: url("$bgImage"); margin: 0; padding:0; } .myPicture { transform: rotate(90deg); background-color: pink; } </style>
                        $logogphc5  $logoGBOS
                        <p id="bgImg" style="font-size: 10px;">
                            <table align="center">
                                <tr>
                                    <td style="text-align: left; width: 70%"><b>&nbsp;&nbsp;Prénom:</b> $prenom<br/><b>&nbsp;&nbsp;Nom:</b> $nom<br/><b>&nbsp;&nbsp;Fonction:</b> $profil<br/><b>&nbsp;&nbsp;Matricule:</b> $login</td>
                                    <td style="text-align: left;">$photoAgent</td>
                                </tr>
                            </table>
                            <p style="background-color: #e5f1dd; text-align: center; font-size: 14px; font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br/>Valable du 15 mai au 14 juin 2023</p>
                                <div> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br/>
                                <b><span style="color: #00b050; font-size: 11px; font-weight: bold;">&nbsp;&nbsp;&nbsp;N° Vert : 800 00 24 24</span> - site web: <u style="color: blue;">www.recensement.sn</u> </b> <br/>
                                </div>
                                <table align="center">
                                    <tr>
                                        <td style="text-align: left; width: 70%"><br/>&nbsp;&nbsp;&nbsp;$mandatImg</td>
                                        <td style="text-align: left;">$qrcodeImg</td>
                                    </tr>
                                    $slogan
                                </table>
                            </p>
                        </p>
                    EOD;
                    // <br/>
                    // <p style="text-align: center; background-color: #00b050; color: white; font-size: 13px; margin: 0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Je suis recensé, je compte !&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>

                    // $pdf->MultiCell(90, 4, $html, 1, 'L', 0, 0, '', '', true, 0, true, false, 0);
                    $pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->MultiCell(90, 0, $html, 1, 'L', 0, 0, '', '', true, 0, true, false, 0);


                    if ($k == 1) {
                        $pdf->writeHTML("<br/><br/>");
                    }
                }

                $index++;
            }

            $pdf->Ln(4);
            if ($addingCount == 4 && ($i + 1) != $totalPage) {
                $pdf->AddPage();
                $pdf->SetLeftMargin(15);
                // $pdf->setCellPaddings(0, 0, 0, 0);
            }
        }


        if (count($contractuels) == 0) {
            $cacrName = $cacr->getSurname() . " | " . $cacr->getCode();

            $pdf->writeHTML("<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>", true, false, false, false, '');
            $pdf->writeHTML("<h1>Aucun agent recenseur retrouvé pour la commune $cacrName</h1>", true, false, false, false, 'C');
        } else {

            $pdf->lastPage();

            // return $pdf->Output('Badge_COMMUNE_' . $cacr->getSurname() . "_" . $cacr->getCode() . '.pdf', 'I');
            try {
                //code...
                $dstFile = $kernel->getProjectDir() . '/var/badges_' . $departement->getCode();

                $filename = 'BADGES_COMMUNE_' . $cacr->getSurname() . "_" . $cacr->getCode() . '.pdf';
                $fileNL = $dstFile . "/" . $filename; //Linux

                $pdf->Output($fileNL, 'F');
            } catch (\Exception $th) {
                //throw $th;
            }
        }
    }


    public static function consentementsAR(
        string $nomDept,
        KernelInterface $kernel,
        CommunesArrCommunautesRurales $cacr,
        UserRepository $userRepo,
        ApplicationsRepository $applicationsRepository,
        CompositionRepository $compositionRepository
    ) {
        // $departement = $acteur->getDepartement()->getSurname();
        $region = $cacr->getEstDirectementRattachDept()
            ? $cacr->getDepartement()->getRegion()->getSurname()
            : $cacr->getCommuneArrondissementVille()->getDepartement()->getRegion()->getSurname();

        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        // $contractuels = $userRepo->findBy(["commune" => $cacr]);
        $contractuels = $compositionRepository->findSupCompositonCCRCA($cacr);
        // var_dump($contractuels); die;
        foreach ($contractuels as $u) {
            $ar = $u->getArr();

            $prenom = $ar->getName();
            $nom = strtoupper($ar->getSurname());
            $tel = $ar->getTelephone();
            $cni = $ar->getCni();

            $arInfo = $applicationsRepository->findOneBy(["nin" => $cni]);

            if ($arInfo != null) {
                $datnaiss = $arInfo->getDateNaissance();
                $datef = $datnaiss->format('d-m-Y');
                $datenaiss = $datef . " à " . $arInfo->getLieuNaissance();
                $lieunaiss =  $arInfo->getLieuNaissance();
            }

            // Generation fichier
            $pdf = new ContratPdf($kernel, 'Fiche de consentement');
            $html =  <<<EOD
             <p class="c19">
             <p>
                 <br><br>Par la présente, je soussigné (e) ($prenom $nom) né (e) le $datenaiss CNI
                 <br>n° $cni
                 <br><br>autorise l’Agence nationale de la Statistique et de la Démographie (GBOS)
                 <br>à utiliser mes données à caractère personnel (Prénoms, nom, date et lieu
                 <br>de naissance, adresse, n° de téléphone, etc.) pour établir un contrat 
                 <br>collectif pour les agents recenseurs et pour toutes autres utilisations dans 
                 <br>le cadre du cinquième Recensement général de la Population et de l’Habitat 
                 (RGPH-5). 
                 <br><br>En foi de quoi je lui délivre cette fiche de consentement attestant mon 
                 <br>accord pour servir et valoir ce que de droit.                    
             </p><br><br>
             
             EOD;

            $pdf->writeHTML($html);

            $pdf->SetRightMargin(25);
            $pdf->writeHTML("Fait à $region, le $df<br>", true, false, false, false, "R");
            $pdf->writeHTML("Signature<br><br>", true, false, false, false, "R");
            $pdf->writeHTML("<br><br>", true, false, false, false, "");

            // saut de Page
            // $pdf->AddPage('L');   

            try {
                $dstFile = $kernel->getProjectDir() . '/var/fiche_consentement_ar_' . $nomDept;
                $filename = 'FICHE_CONSENTEMENT_' . $prenom . "_" . $nom . "_" . $cni . '.pdf';
                $fileNL = $dstFile . "/" . $filename;

                $pdf->Output($fileNL, 'F');
            } catch (\Exception $th) {
                throw $th;
            }

            // $uInfo = $sallesRepo->findOneBy(["login" => $u->getArr()->getEmail()]);
            // if ($uInfo != null) {
            //     $ar = $uInfo->getCandidat();
            //     $info_sup = '';
            //     $sup = $uInfo->getSuperviseur();
            //     if($sup){
            //         $info_sup = $sup->getEmail(). '_'. $sup->getName() . '_' . $sup->getSurname();
            //     }

            //     $prenom = $ar->getName();
            //     $nom = strtoupper($ar->getSurname());
            //     $cni = $ar->getNin();

            //     $datnaiss = $ar->getDateNaissance();
            //     $datef = $datnaiss->format('d-m-Y');
            //     $datenaiss = $datef . " à " . $ar->getLieuNaissance();

            // }
        }
    }

    public function resetFooterText()
    {
        $this->sup = "";
    }
}

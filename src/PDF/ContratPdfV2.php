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

class ContratPdfV2 extends TCPDF
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

        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png', 50, 47, 30, 30, 'PNG', '', 'T', false, 170, 'R', false, false, 0,     false, false, false);
        
        $this->setX(10);
        $this->setY(70);
        $this->write(25, "Je suis recensé (e), je compte !", '', false, "R");

        $this->setX(10);
        $this->setY(90);
        // $this->writeHTML("<h5 style='font-weight:normal !important;'>5<sup>e</sup> Recensement général de la Population et de l'Habitat (RGPH-5)</h5>", false, false, false, false, 'C');

        // $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png', 90, 95, 22, 19, 'PNG');
        // $this->writeHTML("<p/><br/><br/><br/><p/>");
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
                $buildH =  '<th style="width: 40px;">' . ucfirst($h) . "</th>";
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
                    $buildtr = '<td style="width: 40px;">' . $cell . "</td>";
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
            <div style="aligne:center">
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

    // TODO: Contrat collectif | AGENTS RECENSEURS
    public static function prototypeAR(
        string $codeDept,
        KernelInterface $kernel,
        User $superviseur,
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
        $region = $superviseur->getDepartement()->getRegion()->getSurname();

        // $departement = $cacr->getEstDirectementRattachDept()
        //     ? $cacr->getDepartement()->getSurname()
        //     : $cacr->getCommuneArrondissementVille()->getDepartement()->getSurname();

        $departement = $dept->getSurname();

        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        // $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        // $prenom = strtoupper($cacr->getSurname());
        // $nom = strtoupper($cacr->getSurname());

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
        // $cavName = str_pad($cacr->getCommuneArrondissementVille()->getSurname(), 50, ".", STR_PAD_BOTH);
        // $communeName = str_pad($cacr->getSurname(), 50, ".", STR_PAD_BOTH);

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
                repartis en fonction du découpage administratif suivant.
                <br/><br/><b>Région:</b> $regionName
                <br/><b>Département:</b> $departementName
            </p>
        EOD;
        $pdf->writeHTML($html);

        $pdf->AddPage("L");
        // $pdf->writeHTML("<h3>Annexes <br></h3>", true, false, true, false, '');

        // $superviseursDept = $userRepository->findUserByRolesInDepartement("SUPERVISEUR", $dept);
        $countRow = 0;

        $pageAdd = 2;
        $i = 0;

        $contractuels = $compositionRepository->findSupCompositon($superviseur);

        $isFind = false;
        if (count($contractuels) > 0) {
            $data = [];
            foreach ($contractuels as $ar) {
                $profil = $ar->getArr()->getRoles()[0];

                if ($profil != "ROLE_CE") {
                    $isFind = true;
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
            }

            if ($isFind) {
                $headers = ["N°", "Prénom(s) nom", "Date et lieu de naissance", "N° Carte d'Identité", "N° Téléphone", "Signature"];
                $pdf->addTable($headers, $data,  NULL, ucfirst($superviseur->getName()) . ' ' . strtoupper($superviseur->getSurname()) . ' | ' . $superviseur->getEmail());

                $pdf->AddPage("L");
                $pageAdd++;
            }
        }

        $pdf->deletePage($pdf->getPage());

        $pdf->AddPage("P");

        $pdf->resetFooterText();

        $articleHtml =  <<<EOD
            <style> p {font-size: 13px; font-style: normal !important; line-height: 150%;}</style>
            Il a été convenu ce qui suit:
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
                <br/> <br><strong><u>Article 4:</u></strong> 
                    <br>Le présent contrat est conclu pour une durée de un (1) mois. 
                    Les agents recenseurs bénéficiaires de ce présent contrat sont soumis aux mêmes  obligations de réserve et au secret professionnel que les agents de l’GBOS. <br/>
                    L’agent recenseur peut être appelé, dans le cadre des opérations de dénombrement, à exercer leur prestation de services  au-delà des heures normales de travail. <br/>
                    L’agent recenseur est tenu de travailler pendant les jours fériés compte tenu de la spécificité de l’opération.
                <br/><br/><strong><u>Article 5:</u></strong> 
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
        EOD;


        $pdf->writeHTML($articleHtml);

        $pdf->SetRightMargin(25);
        $pdf->writeHTML("Fait à $region, le $df <br/>", true, false, false, false, "R");
        $pdf->writeHTML("<b style='margin-left: 80'>Pour le Directeur général de l’GBOS
        et par délégation
        Le Chef du Service régional de la
        Statistique et de la Démographie</b>", true, false, false, false, "R");

        // $pdf->deletePage($pageAdd);


        try {
            if ($countRow > 0) {
                $dstFile = $kernel->getProjectDir() . '/var/contrat_collectif_' . $codeDept;

                $filename = 'CONTRAT_COLLECTIF_AGENTS_RECENSEURS_' . $superviseur->getEmail() . '.pdf';
                $fileNL = $dstFile . "/" . $filename; //Linux

                $pdf->Output($fileNL, 'F');
            }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    // TODO: Contart collectif | Controleurs
    public static function prototypeCE(
        string $codeDept,
        KernelInterface $kernel,
        User $superviseur,
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

        $region = $superviseur->getDepartement()->getRegion()->getSurname();

        $departement = $superviseur->getDepartement()->getSurname();


        $dt = new DateTime();
        $df = $dt->format("d/m/Y");

        // $candidat = $applicationsRepository->findOneBy(["compte" => $acteur]);

        // $prenom = strtoupper($cacr->getSurname());
        // $nom = strtoupper($cacr->getSurname());

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
        // $cavName = str_pad($cacr->getCommuneArrondissementVille()->getSurname(), 50, ".", STR_PAD_BOTH);
        // $communeName = str_pad($cacr->getSurname(), 50, ".", STR_PAD_BOTH);

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
                sont répartis en fonction du découpage administratif suivant:
                    <br/><br/><b>Région:</b> $regionName
                    <br/><b>Département:</b> $departementName
            </p>
        EOD;
        $pdf->writeHTML($html);

        $pdf->AddPage("L");

        $countRow = 0;

        $pageAdd = 2;

        $i = 0;

        $contractuels = $compositionRepository->findSupCompositon($superviseur);
        $isFind = false;

        if (count($contractuels) > 0) {
            $data = [];
            foreach ($contractuels as $ar) {
                $profil = $ar->getArr()->getRoles()[0];
                if ($profil == "ROLE_CE") {
                    $countRow++;
                    $i++;
                    $isFind = true;
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
            }

            if ($isFind) {
                $headers = ["N°", "Prénom(s) nom", "Date et lieu de naissance", "N° Carte d'Identité", "N° Téléphone", "Signature"];
                $pdf->addTable($headers, $data,  NULL, ucfirst($superviseur->getName()) . ' ' . strtoupper($superviseur->getSurname()) . ' | ' . $superviseur->getEmail());
    
                $pdf->AddPage("L");
                $pageAdd++;
            }
        }

        $pdf->deletePage($pdf->getPage());

        $pdf->AddPage("P");

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
        // $pdf->writeHTML("Fait à $region, le $df <br/>", true, false, false, false, "R");
        // $pdf->writeHTML("<b>Le Chef SRSD de </b> $region", true, false, false, false, "R");
        $pdf->writeHTML('<span>Fait à ' . $region . ', le ' . $df . '<br/>', true, false, false, false, "R");
        $pdf->writeHTML('<b>Pour le Directeur général de l’GBOS <br/><center/>et par délégation </center/><br/> Le Chef du Service régional de la<br/>
        Statistique et de la Démographie</b></span>', true, false, false, false, "R");

        // $pdf->deletePage($pageAdd);

        try {
            if ($countRow > 0) {
                $dstFile = $kernel->getProjectDir() . '/var/contrat_collectif_' . $codeDept;

                $filename = 'CONTRAT_COLLECTIF_CONTROLEURS_' . $superviseur->getEmail() . '.pdf';
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

    public function resetFooterText()
    {
        $this->sup = "";
    }
}

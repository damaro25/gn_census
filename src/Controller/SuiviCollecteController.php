<?php

namespace App\Controller;


use App\Repository\UserRepository;
use App\Repository\RegionsRepository;
use App\Repository\CommunePcpRepository;
use App\Repository\CentroideDrRepository;
use App\Repository\CentroideZcRepository;
use App\Repository\CentroideZsRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\DepartementsRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CentroideParcellairesDrRepository;
use App\Repository\CommunesArrCommunautesRuralesRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class SuiviCollecteController extends AbstractController
{

    public function __construct(ManagerRegistry $registry)
    {
        $this->conn = $registry->getManager()->getConnection();
    }



    public function nb_jour_lancement(
        $date_lancement
    ) {
        $nb_jour = 0;
        $currentDate =  strtotime("now");
        $datediff = $currentDate - $date_lancement;
        $nb_jour = round($datediff / (60 * 60 * 24));
        if($nb_jour > 30){
            $nb_jour = 30;
        }else if($nb_jour < 0){
            $nb_jour = 0;
        }
        return $nb_jour;
    }



    /**
     * @Route("pigor/tableau_de_bord_rgph", name="dashboard_pigor_page2")
     * @IsGranted("ROLE_USER")
     */
    public function dashboardPageRgph(
        Request $request,
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
        DepartementsRepository $deptRepo,
        CommunesArrCommunautesRuralesRepository $comRepo,
    ) {

        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;

        $nbJour = $this->nb_jour_lancement($this->getParameter('date_officielle_lancement'));
        $nbJCollecte = $nbJour - 5;
        $progession_normale = round( ((100 * ($nbJour - 5)) / 21), 1);

        $departements = [];
        $communesAr = [];
        // $nb_zs = 0;
        // $nb_zc = 0;
        // $nb_dr = 0;
        // $nb_conc = 0;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {
            // $nb_zs = count($zsRepo->findAll());
            // $nb_zc = count($zcRepo->findAll());
            // $nb_dr = count($drRepo->findAll());
            // $nb_conc = count($concRepo->findAllParcellaires());
        } elseif ($this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD')) {
            $codReg = substr($me->getDepartement()->getCode(), 0, 2);
            $departements = $deptRepo->findBy(["codeParent" => $codReg], ['code' => "ASC"]);

            // $nb_zs = count($zsRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_zc = count($zcRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_dr = count($drRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_conc = count($concRepo->findAllParcellaires($monCodeDepartement));
            //$nb_conc = count($concRepo->findBy(['codDept' => $monCodeDepartement]));
        } elseif ($this->isGranted('ROLE_CTD')) {
            $codDep = $me->getDepartement()->getCode();
            $communesAr = $comRepo->findDeptCacrs($codDep);
        }

        return $this->render('completude_collecte/data_dashboard_rgph.html.twig', [
            'connectedUser' => $me,
            // 'nb_zs' => $nb_zs,
            // 'nb_zc' => $nb_zc,
            // 'nb_dr' => $nb_dr,
            // 'nb_conc' => $nb_conc,
            'nbJour' => $nbJour,
            'regions' => $regionsRepository->findBy([], ['code' => 'ASC']),
            'departements' => $departements,
            'communesAr' => $communesAr,
            'progession_normale' => ($progession_normale <= 100) ? $progession_normale : 100,
            'nbJCollecte' => $nbJCollecte.'ème jour de collecte',
        ]);
    }



    /**
     * @Route("pigor/tableau_de_bord_stats_indicateurs_cles", name="dashboard_stat_cles_indic")
     */
    public function dashboardPageSatsClesIndic(
        Request $request,
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
        DepartementsRepository $deptRepo,
        CommunesArrCommunautesRuralesRepository $comRepo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ) {

        ini_set('memory_limit', '1024M');

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN') || $this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD') || $this->isGranted('ROLE_CTD')) {
            // $breadcrumbs->addRouteItem("Tableau de bord", 'dashboard_pigor_page2');
        }
        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;


        $departements = [];
        $communesAr = [];

        if ($this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD')) {
            $codReg = substr($me->getDepartement()->getCode(), 0, 2);
            $departements = $deptRepo->findBy(["codeParent" => $codReg], ['code' => "ASC"]);

            // $nb_zs = count($zsRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_zc = count($zcRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_dr = count($drRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_conc = count($concRepo->findAllParcellaires($monCodeDepartement));
            //$nb_conc = count($concRepo->findBy(['codDept' => $monCodeDepartement]));
        } elseif ($this->isGranted('ROLE_CTD')) {
            $codDep = $me->getDepartement()->getCode();
            $communesAr = $comRepo->findDeptCacrs($codDep);
        }

        return $this->render('completude_collecte/dashboard_stats_cles_indic.html.twig', [
            'connectedUser' => $me,
            'regions' => $regionsRepository->findBy([], ['code' => 'ASC']),
            'departements' => $departements,
            'communesAr' => $communesAr,
        ]);
    }



    /**
     * @Route("pigor/tableau_de_bord_stats_concretisation", name="dashboard_stat_concretisation")
     */
    public function dashboardPageSatsConcret(
        Request $request,
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
        DepartementsRepository $deptRepo,
        CommunesArrCommunautesRuralesRepository $comRepo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ) {

        ini_set('memory_limit', '1024M');

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN') || $this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD') || $this->isGranted('ROLE_CTD')) {
            // $breadcrumbs->addRouteItem("Tableau de bord", 'dashboard_pigor_page2');
        }
        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;


        $departements = [];
        $communesAr = [];

        if ($this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD')) {
            $codReg = substr($me->getDepartement()->getCode(), 0, 2);
            $departements = $deptRepo->findBy(["codeParent" => $codReg], ['code' => "ASC"]);

            // $nb_zs = count($zsRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_zc = count($zcRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_dr = count($drRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_conc = count($concRepo->findAllParcellaires($monCodeDepartement));
            //$nb_conc = count($concRepo->findBy(['codDept' => $monCodeDepartement]));
        } elseif ($this->isGranted('ROLE_CTD')) {
            $codDep = $me->getDepartement()->getCode();
            $communesAr = $comRepo->findDeptCacrs($codDep);
        }

        return $this->render('completude_collecte/dashboard_stats_concretisation.html.twig', [
            'connectedUser' => $me,
            'regions' => $regionsRepository->findBy([], ['code' => 'ASC']),
            'departements' => $departements,
            'communesAr' => $communesAr,
        ]);
    }



    /**
     * @Route("pigor/tableau_de_bord_stats_interviews", name="dashboard_stat_interviews")
     * @IsGranted("ROLE_USER")
     */
    public function dashboardPageSatsInter(
        Request $request,
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
        DepartementsRepository $deptRepo,
        CommunesArrCommunautesRuralesRepository $comRepo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ) {

        ini_set('memory_limit', '1024M');

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN') || $this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD') || $this->isGranted('ROLE_CTD')) {
            // $breadcrumbs->addRouteItem("Tableau de bord", 'dashboard_pigor_page2');
        }
        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;


        $departements = [];
        $communesAr = [];

        if ($this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD')) {
            $codReg = substr($me->getDepartement()->getCode(), 0, 2);
            $departements = $deptRepo->findBy(["codeParent" => $codReg], ['code' => "ASC"]);

            // $nb_zs = count($zsRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_zc = count($zcRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_dr = count($drRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_conc = count($concRepo->findAllParcellaires($monCodeDepartement));
            //$nb_conc = count($concRepo->findBy(['codDept' => $monCodeDepartement]));
        } elseif ($this->isGranted('ROLE_CTD')) {
            $codDep = $me->getDepartement()->getCode();
            $communesAr = $comRepo->findDeptCacrs($codDep);
        }

        return $this->render('completude_collecte/dashboard_stats_interviews.html.twig', [
            'connectedUser' => $me,
            'regions' => $regionsRepository->findBy([], ['code' => 'ASC']),
            'departements' => $departements,
            'communesAr' => $communesAr,
        ]);
    }

    /**
     * @Route("pigor/tableau_de_bord_repport", name="dashboard_repports")
     */
    public function dashboardPageRepport(
        Request $request,
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
        DepartementsRepository $deptRepo,
        CommunesArrCommunautesRuralesRepository $comRepo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ) {

        ini_set('memory_limit', '1024M');

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN') || $this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD') || $this->isGranted('ROLE_CTD')) {
            // $breadcrumbs->addRouteItem("Tableau de bord", 'dashboard_pigor_page2');
        }
        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;


        $departements = [];
        $communesAr = [];

        if ($this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD')) {
            $codReg = substr($me->getDepartement()->getCode(), 0, 2);
            $departements = $deptRepo->findBy(["codeParent" => $codReg], ['code' => "ASC"]);

            // $nb_zs = count($zsRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_zc = count($zcRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_dr = count($drRepo->findBy(['codDept' => $monCodeDepartement]));
            // $nb_conc = count($concRepo->findAllParcellaires($monCodeDepartement));
            //$nb_conc = count($concRepo->findBy(['codDept' => $monCodeDepartement]));
        } elseif ($this->isGranted('ROLE_CTD')) {
            $codDep = $me->getDepartement()->getCode();
            $communesAr = $comRepo->findDeptCacrs($codDep);
        }

        return $this->render('completude_collecte/dashboard_repports.html.twig', [
            'connectedUser' => $me,
            'regions' => $regionsRepository->findBy([], ['code' => 'ASC']),
            'departements' => $departements,
            'communesAr' => $communesAr,
        ]);
    }


    /**
     * @Route("pigor/tableau_de_bord", name="dashboard_pigor_page")
     * @IsGranted("ROLE_USER")
     */
    public function dashboardPage(
        Request $request,
        UserRepository $userRepo,
        CentroideZsRepository $zsRepo,
        CentroideZcRepository $zcRepo,
        CentroideDrRepository $drRepo,
        CentroideParcellairesDrRepository $concRepo

    ) {

        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;

        $nb_zs = 0;
        $nb_zc = 0;
        $nb_dr = 0;
        $nb_conc = 0;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')) {
            $nb_zs = count($zsRepo->findAll());
            $nb_zc = count($zcRepo->findAll());
            $nb_dr = count($drRepo->findAll());
            $nb_conc = count($concRepo->findAllParcellaires());
        } else {
            $nb_zs = count($zsRepo->findBy(['codDept' => $monCodeDepartement]));
            $nb_zc = count($zcRepo->findBy(['codDept' => $monCodeDepartement]));
            $nb_dr = count($drRepo->findBy(['codDept' => $monCodeDepartement]));
            $nb_conc = count($concRepo->findAllParcellaires($monCodeDepartement));
            //$nb_conc = count($concRepo->findBy(['codDept' => $monCodeDepartement]));
        }

        return $this->render('completude_collecte/dashboard.html.twig', [
            'connectedUser' => $me,
            'nb_zs' => $nb_zs,
            'nb_zc' => $nb_zc,
            'nb_dr' => $nb_dr,
            'nb_conc' => $nb_conc,

        ]);
    }


    /**
     * 
     * @Route("/pyramide_des_ages/{codeDep}", name="pyramide_repartition_par_age",methods={"GET"},options={"expose"=true})
     * @IsGranted("ROLE_USER")
     *
     */
    public function JsonPyramideDesAges($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT for_order, categorie, SUM(hommes) AS hommes, SUM(femmes) AS femmes FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS categorie,
                    SUM(case when cr.ext_b06 = 1 then 1 else 0 end) AS hommes,
                    SUM(case when cr.ext_b06 = 2 then 1 else 0 end) AS femmes
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 GROUP BY cr.ext_b08
            ) Sortie GROUP BY for_order,categorie ORDER BY for_order DESC
            ";
        } else {
            $requete = "
            SELECT for_order, categorie, SUM(hommes) AS hommes, SUM(femmes) AS femmes FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS categorie,
                    SUM(case when cr.ext_b06 = 1 then 1 else 0 end) AS hommes,
                    SUM(case when cr.ext_b06 = 2 then 1 else 0 end) AS femmes
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 AND cl.ext_b0_iddr LIKE '" . $codeDep . "%' GROUP BY cr.ext_b08
            ) Sortie GROUP BY for_order,categorie ORDER BY for_order DESC
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        $tabAgeHomme = [];
        $tabAgeFemme = [];
        $tabCategorie = [];

        foreach ($stmt as $oneRep) {
            array_push($tabAgeHomme, $oneRep['hommes']);
            array_push($tabAgeFemme, $oneRep['femmes']);
            array_push($tabCategorie, $oneRep['categorie']);
        }

        return new JsonResponse(array('for_male' => $tabAgeHomme, 'for_female' => $tabAgeFemme, 'groupe_age' => $tabCategorie));
    }



    /**
     * 
     * 
     * @Route("/getStatsClesIndic/{codeDep}", name="get_stat_cles_indic_tab",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonGetTabStatClesIndic($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT COUNT(*) AS total_men,
            SUM(case when men_rec.is_agriculture = 1 then 1 else 0 end) AS men_agricoles,
            SUM(case when men_rec.is_deces = 1 then 1 else 0 end) AS men_with_deces,
            SUM(case when men_rec.is_emigration = 1 then 1 else 0 end) AS men_with_emigration,
            SUM(men_rec.men_taille_reel) AS population,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.is_agriculture = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_agri,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.is_deces = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_deces,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.is_emigration = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_emig,
            COALESCE(ROUND((CAST(SUM(men_rec.men_taille_reel) AS float) / CAST(COUNT(*) AS float)), 0), 0) AS taille_moy_men
            FROM [menage_cases] men_case
        JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
        JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
        WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 AND men_rec.men_statut IN (2, 3)
            ";
        } else {
            $requete = "
            SELECT COUNT(*) AS total_men,
                SUM(case when men_rec.is_agriculture = 1 then 1 else 0 end) AS men_agricoles,
                SUM(case when men_rec.is_deces = 1 then 1 else 0 end) AS men_with_deces,
                SUM(case when men_rec.is_emigration = 1 then 1 else 0 end) AS men_with_emigration,
				SUM(men_rec.men_taille_reel) AS population,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.is_agriculture = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_agri,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.is_deces = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_deces,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.is_emigration = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_emig,
                COALESCE(ROUND((CAST(SUM(men_rec.men_taille_reel) AS float) / CAST(COUNT(*) AS float)), 0), 0) AS taille_moy_men
                FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 AND men_rec.men_statut IN (2, 3) AND men_lev.men_iddr LIKE '" . $codeDep . "%'
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);
        $oneRep = $stmt[0];

        return new JsonResponse(array(
            'total_men' => $oneRep['total_men'], 'men_agricoles' => $oneRep['men_agricoles'], 'men_with_deces' => $oneRep['men_with_deces'],
            'men_with_emigration' => $oneRep['men_with_emigration'], 'population' => $oneRep['population'], 'perc_agri' => $oneRep['perc_agri'],
            'perc_deces' => $oneRep['perc_deces'], 'perc_emig' => $oneRep['perc_emig'], 'taille_moy_men' => $oneRep['taille_moy_men']
        ));
    }


    /**
     * 
     * 
     * @Route("/getStatsConcretisation/{codeDep}", name="get_stat_concretisation_tab",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonGetTabSatConcretisation($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
                SELECT count(*) AS total,
                SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS inchanges,
                SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS nouveaux,
                SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS voyages,
                SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS demenages,
                SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS erreurs,
                SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS nexisteplus,
                SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS cedespcp,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_inchanges,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nouveaux,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_voyages,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_demenages,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_erreurs,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nexisteplus,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_cedespcp
            FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0
            ";
        } else {
            $requete = "
            SELECT count(*) AS total,
                SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS inchanges,
                SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS nouveaux,
                SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS voyages,
                SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS demenages,
                SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS erreurs,
                SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS nexisteplus,
                SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS cedespcp,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_inchanges,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nouveaux,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_voyages,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_demenages,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_erreurs,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nexisteplus,
                COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_cedespcp
            FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0 AND men_lev.men_iddr LIKE '" . $codeDep . "%'
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);
        $oneRep = $stmt[0];

        return new JsonResponse(array(
            'total' => $oneRep['total'], 'inchanges' => $oneRep['inchanges'], 'nouveaux' => $oneRep['nouveaux'],
            'voyages' => $oneRep['voyages'], 'demenages' => $oneRep['demenages'], 'erreurs' => $oneRep['erreurs'],
            'nexisteplus' => $oneRep['nexisteplus'], 'cedespcp' => $oneRep['cedespcp'], 'perc_inchanges' => $oneRep['perc_inchanges'], 'perc_nouveaux' => $oneRep['perc_nouveaux'],
            'perc_voyages' => $oneRep['perc_voyages'], 'perc_demenages' => $oneRep['perc_demenages'], 'perc_erreurs' => $oneRep['perc_erreurs'],
            'perc_nexisteplus' => $oneRep['perc_nexisteplus'], 'perc_cedespcp' => $oneRep['perc_cedespcp']
        ));
    }



    /**
     * 
     * @Route("/getStatsInterviews/{codeDep}", name="get_stat_interview_tab",methods={"GET"},options={"expose"=true})
     * @IsGranted("ROLE_USER")
     *
     */
    public function JsonGetTabSatInterviews($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT count(*) AS tou,
            COALESCE(SUM(case when men_rec.men_statut >= 2 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS total,
            COALESCE(SUM(case when men_rec.men_statut = 2 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS partiels,
            COALESCE(SUM(case when men_rec.men_etat != 1 then 1 else 0 end), 0) AS attendu,
            COALESCE(SUM(case when men_rec.men_statut = 3 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS complets,
            COALESCE(SUM(case when men_rec.men_statut = 1 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS no_do,
            COALESCE(SUM(case when men_rec.men_statut = 4 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS concret
            FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0
            ";
            $reqDouble = "
            SELECT COUNT(*) AS inDouble FROM (
                SELECT COUNT(*) AS presence
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 GROUP BY men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num,men_taille, men_cm) Isdouble
                WHERE presence > 1
                ";

            $far = "
            SELECT COUNT(*) AS far
                FROM [fiche_cons_level-1] lev
                JOIN fiche_cons_ext_cons rec ON rec.[level-1-id] = lev.[level-1-id]
                WHERE rec.cons_dis_cr > 50
            ";

            $reqDureeMoy = "
                SELECT COUNT(*) AS nb_men, SUM((CAST(r.men_duration AS BIGINT)) / 60) as total_interview, COALESCE((SUM(CAST(r.men_duration AS BIGINT) / 60) / COUNT(*)), 0) AS duree_moy
                FROM menage_cases c JOIN [menage_level-1] l ON l.[case-id]=c.id 
                            JOIN menage_menage_rec r ON r.[level-1-id]=l.[level-1-id] WHERE c.deleted=0 AND r.men_etat < 7 AND r.men_statut IN (2, 3)
                    ";
        } else {
            $requete = "
            SELECT count(*) AS tou,
            COALESCE(SUM(case when men_rec.men_statut >= 2 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS total,
            COALESCE(SUM(case when men_rec.men_statut = 2 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS partiels,
			COALESCE(SUM(case when men_rec.men_etat != 1 then 1 else 0 end), 0) AS attendu,
            COALESCE(SUM(case when men_rec.men_statut = 3 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS complets,
            COALESCE(SUM(case when men_rec.men_statut = 1 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS no_do,
            COALESCE(SUM(case when men_rec.men_statut = 4 AND men_rec.men_etat < 7 then 1 else 0 end), 0) AS concret
            FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0 AND men_lev.men_iddr LIKE '" . $codeDep . "%'
            ";
            $reqDouble = "
            SELECT COUNT(*) AS inDouble FROM (
                SELECT COUNT(*) AS presence
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 AND men_lev.men_iddr LIKE '" . $codeDep . "%' GROUP BY men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num) Isdouble
                WHERE presence > 1
                ";

                $far = "
                SELECT COUNT(*) AS far
                FROM [fiche_cons_level-1] lev
                JOIN fiche_cons_ext_cons rec ON rec.[level-1-id] = lev.[level-1-id]
                WHERE rec.cons_dis_cr > 50 AND lev.cons_iddr LIKE '" . $codeDep . "%' 
                ";
    
            $reqDureeMoy = "
                SELECT COUNT(*) AS nb_men, SUM((CAST(r.men_duration AS BIGINT)) / 60) as total_interview, COALESCE((SUM(CAST(r.men_duration AS BIGINT) / 60) / COUNT(*)), 0) AS duree_moy
                FROM menage_cases c JOIN [menage_level-1] l ON l.[case-id]=c.id 
                            JOIN menage_menage_rec r ON r.[level-1-id]=l.[level-1-id] WHERE c.deleted=0 AND r.men_etat < 7 AND l.men_iddr LIKE '" . $codeDep . "%'                    ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);
        $oneRep = $stmt[0];

        $stmtD = $this->conn->fetchAllAssociative($reqDouble);
        $DoubleoneRep = $stmtD[0];

        $stmtF = $this->conn->fetchAllAssociative($far);
        $FaroneRep = $stmtF[0];

        $stmtDuree = $this->conn->fetchAllAssociative($reqDureeMoy);
        $DureeMyoneRep = $stmtDuree[0];

        return new JsonResponse(array(
            'total' => $oneRep['total'], 'attendu' => $oneRep['attendu'], 'partiel' => $oneRep['partiels'], 'complet' => $oneRep['complets'], 'doublon' => $DoubleoneRep['inDouble'],
            'far' => $FaroneRep['far'], 'no_do' => $oneRep['no_do'], 'concretise' => $oneRep['concret'], 'duree_interv' => $DureeMyoneRep['duree_moy']
        ));
    }



    /**
     * @Route("/getPopulation/{codeDep}", name="get_population",methods={"GET"},options={"expose"=true})
     * @IsGranted("ROLE_USER")
     *
     */
    public function JsonGetPop($codeDep)
    {

        if ($codeDep == 'national') {
            $reqCarto = "
            SELECT COALESCE(SUM(men_taille), 0) AS cartographie FROM
			(SELECT men_iddr, men_id_edif, men_num, men_taille
                FROM [menage_cases] mc JOIN [menage_level-1] ml
                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 AND mr.men_etat != 1
				GROUP BY men_iddr, men_id_edif, men_num, men_taille) distnct
            ";

            $reqAttendu = "
            SELECT COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu FROM
			(
			SELECT men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                FROM [menage_cases] mc JOIN [menage_level-1] ml
                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 AND mr.men_etat < 7 
				GROUP BY men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
				) dst
            ";

            $reqCollecte = "
            SELECT COALESCE(SUM(men_taille_reel), 0) AS collecte FROM (
                SELECT men_iddr, men_id_edif, men_num, men_taille_reel
                        FROM [menage_cases] mc JOIN [menage_level-1] ml
                        ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                        ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3)
                        GROUP BY men_iddr, men_id_edif, men_num, men_taille_reel) dstin
            ";

            $requete = "
            SELECT COALESCE(SUM(men_taille), 0) AS attendu,
                COALESCE(SUM(men_taille_reel), 0) AS collecte,
                ROUND(((CAST(SUM(men_taille_reel) AS float) / CAST(SUM(men_taille) AS float))*100), 1) AS pourcent FROM
				(SELECT men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                FROM [menage_cases] mc JOIN [menage_level-1] ml
                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0
				GROUP BY men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
            ";
        } else {
            $reqCarto = "
            SELECT COALESCE(SUM(men_taille), 0) AS cartographie FROM
			(SELECT men_iddr, men_id_edif, men_num, men_taille
                FROM [menage_cases] mc JOIN [menage_level-1] ml
                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 AND mr.men_etat != 1 
				AND ml.men_iddr LIKE '" . $codeDep . "%'
				GROUP BY men_iddr, men_id_edif, men_num, men_taille) distnct
            ";

            $reqAttendu = "
            SELECT COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu FROM
			(
			SELECT men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                FROM [menage_cases] mc JOIN [menage_level-1] ml
                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 AND mr.men_etat < 7 
                AND ml.men_iddr LIKE '" . $codeDep . "%'
				GROUP BY men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
				) dst
            ";

            $reqCollecte = "
            SELECT COALESCE(SUM(men_taille_reel), 0) AS collecte FROM (
                SELECT men_iddr, men_id_edif, men_num, men_taille_reel
                        FROM [menage_cases] mc JOIN [menage_level-1] ml
                        ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                        ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3)
                        AND ml.men_iddr LIKE '" . $codeDep . "%'
                        GROUP BY men_iddr, men_id_edif, men_num, men_taille_reel) dstin
            ";

            $requete = "		
            SELECT COALESCE(SUM(men_taille), 0) AS attendu,
                COALESCE(SUM(men_taille_reel), 0) AS collecte,
                ROUND(((CAST(SUM(men_taille_reel) AS float) / CAST(SUM(men_taille) AS float))*100), 1) AS pourcent FROM
				(SELECT men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                FROM [menage_cases] mc JOIN [menage_level-1] ml
                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0
                AND ml.men_iddr LIKE '" . $codeDep . "%'
				GROUP BY men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);
        $oneRep = $stmt[0];

        $stmtCarto = $this->conn->fetchAllAssociative($reqCarto);
        $oneRepCarto = $stmtCarto[0];

        $stmtAttendu = $this->conn->fetchAllAssociative($reqAttendu);
        $oneRepAttendu = $stmtAttendu[0];

        $stmtCollecte = $this->conn->fetchAllAssociative($reqCollecte);
        $oneRepCollecte = $stmtCollecte[0];

        $pourcentage = 0;
        if ($oneRepAttendu['attendu'] != 0) {
            $pourcentage = round(($oneRepCollecte['collecte'] / $oneRepAttendu['attendu']) * 100, 1);
        }

        $couverture = 0;
        if ($oneRepCarto['cartographie'] != 0) {
            $couverture = round(($oneRepCollecte['collecte'] / $oneRepCarto['cartographie']) * 100, 1);
        }

        return new JsonResponse(array(
            'pop' => $oneRepCollecte['collecte'], 'perc' => $pourcentage, 'couverture' => $couverture,
            'attente' => $oneRepAttendu['attendu'], 'cartographie' => $oneRepCarto['cartographie']
        ));
    }


    /**
     * 
     * 
     * @Route("/tableau_stats_statuts_menage/{codeDep}", name="tab_stats_status_menage",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonTabStatStatutsMen($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT leDr.CCRCA AS commune, leDr.COD_DR_2022 AS codedr, leDr.COD_ZSUP AS zs, leDr.COD_ZCONT AS zc, count(*) AS total,
              SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS inchanges,
              SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS nouveaux,
			  SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS voyages,
			  SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS demenages,
			  SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS erreurs,
			  SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS nexisteplus,
			  SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS cedespcp,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_inchanges,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nouveaux,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_voyages,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_demenages,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_erreurs,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nexisteplus,
			  COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_cedespcp
           FROM [menage_cases] men_case
           JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
           JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
           JOIN CENTROIDE_DR leDr ON leDr.COD_DR_2022  = men_lev.men_iddr
           WHERE men_case.deleted = 0 GROUP BY leDr.COD_DR_2022,leDr.CCRCA,leDr.COD_ZSUP,leDr.COD_ZCONT
            ";
        } else {
            $requete = "SELECT leDr.CCRCA AS commune, leDr.COD_DR_2022 AS codedr, leDr.COD_ZSUP AS zs, leDr.COD_ZCONT AS zc, count(*) AS total,
            SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS inchanges,
            SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS nouveaux,
            SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS voyages,
            SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS demenages,
            SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS erreurs,
            SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS nexisteplus,
            SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS cedespcp,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 0 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_inchanges,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 1 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nouveaux,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 6 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_voyages,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 7 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_demenages,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 8 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_erreurs,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 9 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_nexisteplus,
            COALESCE(ROUND(((CAST(SUM(case when men_rec.men_etat = 5 then 1 else 0 end) AS float) / CAST(COUNT(*) AS float))*100), 1), 0) AS perc_cedespcp
         FROM [menage_cases] men_case
         JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
         JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
         JOIN CENTROIDE_DR leDr ON leDr.COD_DR_2022  = men_lev.men_iddr
         WHERE men_case.deleted = 0 AND men_lev.men_iddr LIKE '" . $codeDep . "%' GROUP BY leDr.COD_DR_2022,leDr.CCRCA,leDr.COD_ZSUP,leDr.COD_ZCONT";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        return new JsonResponse($stmt);
    }



    /**
     * 
     * 
     * @Route("/tableau_stats_collecte_menage/{codeDep}", name="tab_stats_collecte_menage",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonTabStatCollecteMen($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT col.commune, col.codedr, col.zs, col.zc, col.total, col.attendu, col.partiels, col.complets, col.completude, COALESCE(doub.inDouble, 0) AS inDouble
            FROM
           (SELECT leDr.CCRCA AS commune, leDr.COD_DR_2022 AS codedr, leDr.COD_ZSUP AS zs, leDr.COD_ZCONT AS zc, count(*) AS tou,
              SUM(case when men_rec.men_etat < 7 then 1 else 0 end) AS total,
			  SUM(case when men_rec.men_etat != 1 then 1 else 0 end) AS attendu,
              SUM(case when men_rec.men_statut = 2 AND men_rec.men_etat < 7 then 1 else 0 end) AS partiels,
              SUM(case when men_rec.men_statut = 3 AND men_rec.men_etat < 7 then 1 else 0 end) AS complets,
			  ROUND(((CAST(SUM(case when men_rec.men_statut = 3 AND men_rec.men_etat < 7 then 1 else 0 end) AS float) / CAST(SUM(case when men_rec.men_etat < 7 then 1 else 0 end) AS float))*100), 1) AS completude 
           FROM [menage_cases] men_case
           JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
           JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
           JOIN CENTROIDE_DR leDr ON leDr.COD_DR_2022  = men_lev.men_iddr
           WHERE men_case.deleted = 0 GROUP BY leDr.COD_DR_2022,leDr.CCRCA,leDr.COD_ZSUP,leDr.COD_ZCONT) col
           LEFT JOIN
         (
           SELECT COUNT(*) AS inDouble, codedr FROM (
           SELECT COUNT(*) AS presence, leDr.COD_DR_2022 AS codedr
           FROM [menage_cases] men_case
           JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
           JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
           JOIN CENTROIDE_DR leDr ON leDr.COD_DR_2022  = men_lev.men_iddr
           WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 GROUP BY leDr.COD_DR_2022, men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num) Isdouble
           WHERE presence > 1 GROUP BY codedr
         ) doub ON doub.codedr = col.codedr ORDER BY codedr
            ";
        } else {
            $requete = "SELECT col.commune, col.codedr, col.zs, col.zc, col.total, col.attendu, col.partiels, col.complets, col.completude, COALESCE(doub.inDouble, 0) AS inDouble
               FROM
              (SELECT leDr.CCRCA AS commune, leDr.COD_DR_2022 AS codedr, leDr.COD_ZSUP AS zs, leDr.COD_ZCONT AS zc, count(*) AS tou,
                 SUM(case when men_rec.men_etat < 7 then 1 else 0 end) AS total,
				 SUM(case when men_rec.men_etat != 1 then 1 else 0 end) AS attendu,
                 SUM(case when men_rec.men_statut = 2 AND men_rec.men_etat < 7 then 1 else 0 end) AS partiels,
                 SUM(case when men_rec.men_statut = 3 AND men_rec.men_etat < 7 then 1 else 0 end) AS complets,
                  ROUND(((CAST(SUM(case when men_rec.men_statut = 3 AND men_rec.men_etat < 7 then 1 else 0 end) AS float) / CAST(SUM(case when men_rec.men_etat < 7 then 1 else 0 end) AS float))*100), 1) AS completude 
            FROM [menage_cases] men_case
           JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
           JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
           JOIN CENTROIDE_DR leDr ON leDr.COD_DR_2022  = men_lev.men_iddr
           WHERE men_case.deleted = 0 AND men_lev.men_iddr LIKE '" . $codeDep . "%' GROUP BY leDr.COD_DR_2022,leDr.CCRCA,leDr.COD_ZSUP,leDr.COD_ZCONT) col
           LEFT JOIN
         (
           SELECT COUNT(*) AS inDouble, codedr FROM (
           SELECT COUNT(*) AS presence, leDr.COD_DR_2022 AS codedr
           FROM [menage_cases] men_case
           JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
           JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
           JOIN CENTROIDE_DR leDr ON leDr.COD_DR_2022  = men_lev.men_iddr
           WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 AND men_lev.men_iddr LIKE '" . $codeDep . "%' GROUP BY leDr.COD_DR_2022, men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num) Isdouble
           WHERE presence > 1 GROUP BY codedr
         ) doub ON doub.codedr = col.codedr ORDER BY codedr";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        return new JsonResponse($stmt);
    }


    /**
     * 
     * 
     * @Route("/stats_collecte_menage/{codeDep}", name="bar_stats_collecte_menage",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonBarStatCollecteMen($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT col.region, col.total, col.partiels, col.complets, COALESCE(doub.inDouble, 0) AS inDouble
                FROM
                (SELECT reg.nom AS region, count(*) AS total,
                    SUM(case when men_rec.men_statut = 2 then 1 else 0 end) AS partiels,
                    SUM(case when men_rec.men_statut = 3 then 1 else 0 end) AS complets
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                JOIN regions reg ON reg.code = SUBSTRING(men_lev.men_iddr, 1, 2)
                WHERE men_case.deleted = 0 GROUP BY reg.nom) col
                LEFT JOIN
                (
                SELECT COUNT(*) AS inDouble, region FROM (
                SELECT COUNT(*) AS presence, reg.nom AS region
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                JOIN regions reg ON reg.code = SUBSTRING(men_lev.men_iddr, 1, 2)
                WHERE men_case.deleted = 0 GROUP BY reg.nom, men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num) Isdouble
                WHERE presence > 1 GROUP BY region
                ) doub ON doub.region = col.region
            ";
        } else {

            //A intégrer au besoin
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        $tabReg = [];
        $tabTotal = [];
        $tabPartiels = [];
        $tabComplets = [];
        $tabInDouble = [];

        foreach ($stmt as $oneRep) {
            array_push($tabReg, $oneRep['region']);
            array_push($tabTotal, $oneRep['total']);
            array_push($tabPartiels, $oneRep['partiels']);
            array_push($tabComplets, $oneRep['complets']);
            array_push($tabInDouble, $oneRep['inDouble']);
        }

        return new JsonResponse(array('region' => $tabReg, 'total' => $tabTotal, 'partiels' => $tabPartiels, 'complets' => $tabComplets, 'inDouble' => $tabInDouble));
    }


    /**
     * 
     * 
     * @Route("/progession_pop_bis/{codeDep}", name="progression_pop_bis",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonProgessionBis($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
            COALESCE(ROUND(((CAST(col.collecte AS float) / CAST(att.attendu AS float))*100), 1), 0) AS portion  FROM
            (SELECT region,
                            COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu FROM
							(SELECT reg.nom AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id]
                            JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 
							 GROUP BY reg.nom, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                            GROUP BY region ) att
        LEFT JOIN
                (	SELECT region,
                    COALESCE(SUM(men_taille_reel), 0) AS collecte FROM
							(SELECT reg.nom AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                    FROM [menage_cases] mc JOIN [menage_level-1] ml
                    ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                    ON ml.[level-1-id] = mr.[level-1-id]
                    JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3)
                   GROUP BY reg.nom, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
				   GROUP BY region) col
            ON att.region = col.region ORDER BY att.region
            ";
        } else {
            //     $requete = "
            //     SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
            //     COALESCE(ROUND(((CAST(col.collecte AS float) / CAST(att.attendu AS float))*100), 1), 0) AS portion  FROM
            //     (SELECT reg.nom AS region,
            //                     COALESCE(SUM(mr.men_taille), 0) AS attendu
            //                     FROM [menage_cases] mc JOIN [menage_level-1] ml
            //                     ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
            //                     ON ml.[level-1-id] = mr.[level-1-id]
            //                     JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND reg.code = SUBSTRING('". $codeDep . "', 1, 2)
            //                     GROUP BY reg.nom ) att
            // LEFT JOIN
            //         (	SELECT reg.nom AS region,
            //             COALESCE(SUM(mr.men_taille_reel), 0) AS collecte
            //             FROM [menage_cases] mc JOIN [menage_level-1] ml
            //             ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
            //             ON ml.[level-1-id] = mr.[level-1-id]
            //             JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3) AND reg.code = SUBSTRING('". $codeDep . "', 1, 2)
            //             GROUP BY reg.nom) col
            //     ON att.region = col.region ORDER BY att.region
            //     ";


            if (strlen($codeDep) == 2) {
                $requete = "
            SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
            COALESCE(ROUND(((CAST(col.collecte AS float) / CAST(att.attendu AS float))*100), 1), 0) AS portion  FROM
            (SELECT region,
                            COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu FROM
                        (SELECT dept.nom AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id]
                            JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
                            JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
                            JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
                            JOIN regions reg ON reg.id = dept.region_id
                            WHERE mc.deleted = 0 AND mr.men_etat < 7 AND ml.men_iddr LIKE '" . $codeDep . "%'
                            GROUP BY dept.nom, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                            GROUP BY region ) att
        LEFT JOIN
                (	SELECT region,
                    COALESCE(SUM(men_taille_reel), 0) AS collecte
                    FROM
                        (SELECT dept.nom AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                    FROM [menage_cases] mc JOIN [menage_level-1] ml
                    ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                    ON ml.[level-1-id] = mr.[level-1-id]
                    JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
                    JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
                    JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
                    JOIN regions reg ON reg.id = dept.region_id
                    WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3) AND ml.men_iddr LIKE '" . $codeDep . "%'
                    GROUP BY dept.nom, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
                    GROUP BY region) col
            ON att.region = col.region ORDER BY att.region
                ";
            } elseif (strlen($codeDep) == 3) {
                $requete = "
                SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
                COALESCE(ROUND(((CAST(col.collecte AS float) / CAST(att.attendu AS float))*100), 1), 0) AS portion  FROM
                (SELECT region,
                                COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu FROM
							(SELECT commR.nom AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                                FROM [menage_cases] mc JOIN [menage_level-1] ml
                                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                                ON ml.[level-1-id] = mr.[level-1-id]
                                JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
                                JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
                                JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
                                JOIN regions reg ON reg.id = dept.region_id
                                WHERE mc.deleted = 0 AND mr.men_etat < 7 AND ml.men_iddr LIKE '" . $codeDep . "%'
								GROUP BY commR.nom, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                                GROUP BY region ) att
            LEFT JOIN
                    (	SELECT region,
                        COALESCE(SUM(men_taille_reel), 0) AS collecte
						 FROM
							(SELECT commR.nom AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                        FROM [menage_cases] mc JOIN [menage_level-1] ml
                        ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                        ON ml.[level-1-id] = mr.[level-1-id]
                         JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
                         JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
                         JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
                         JOIN regions reg ON reg.id = dept.region_id
                        WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3) AND ml.men_iddr LIKE '" . $codeDep . "%'
						GROUP BY commR.nom, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
                        GROUP BY region) col
                ON att.region = col.region ORDER BY att.region
                ";
            } elseif (strlen($codeDep) == 8) {
                $requete = "
                SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
                COALESCE(ROUND(((CAST(col.collecte AS float) / NULLIF(CAST(att.attendu AS float), 0))*100), 1), 0) AS portion  FROM
                (SELECT region,
                                COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu FROM
                                (SELECT centdr.COD_DR_2022 AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                                FROM [menage_cases] mc JOIN [menage_level-1] ml
                                ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                                ON ml.[level-1-id] = mr.[level-1-id]
                                JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
                                JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
                                JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
                                JOIN regions reg ON reg.id = dept.region_id
                                WHERE mc.deleted = 0 AND mr.men_etat < 7 AND ml.men_iddr LIKE '" . $codeDep . "%'
                                GROUP BY centdr.COD_DR_2022, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                                GROUP BY region ) att
            LEFT JOIN
                    (	SELECT region,
                        COALESCE(SUM(men_taille_reel), 0) AS collecte FROM
                                (SELECT centdr.COD_DR_2022 AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                        FROM [menage_cases] mc JOIN [menage_level-1] ml
                        ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                        ON ml.[level-1-id] = mr.[level-1-id]
                         JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
                         JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
                         JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
                         JOIN regions reg ON reg.id = dept.region_id
                        WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3) AND ml.men_iddr LIKE '" . $codeDep . "%'
                        GROUP BY centdr.COD_DR_2022, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
                        GROUP BY region) col
                ON att.region = col.region ORDER BY att.region
                ";
            } elseif (strlen($codeDep) == 12) {
                $requete = "
                SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
            COALESCE(ROUND(((CAST(col.collecte AS float) / NULLIF(CAST(att.attendu AS float), 0))*100), 1), 0) AS portion  FROM
            (SELECT region,
                            COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu 
							FROM
							(SELECT centdr.COD_DR_2022 AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id]
                            JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
							JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
							JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
							JOIN regions reg ON reg.id = dept.region_id
							WHERE mc.deleted = 0 AND mr.men_etat < 7 AND ml.men_iddr LIKE '" . $codeDep . "%'
							GROUP BY centdr.COD_DR_2022, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                            GROUP BY region ) att
        LEFT JOIN
                (	SELECT region,
                    COALESCE(SUM(men_taille_reel), 0) AS collecte FROM
							(SELECT centdr.COD_DR_2022 AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                    FROM [menage_cases] mc JOIN [menage_level-1] ml
                    ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                    ON ml.[level-1-id] = mr.[level-1-id]
                     JOIN CENTROIDE_DR centdr ON centdr.COD_DR_2022 = ml.men_iddr
					 JOIN communes_arr_communautes_rurales commR ON commR.code = centdr.COD_CCRCA
					 JOIN departements dept ON dept.code = SUBSTRING(commR.code, 1, 3)
					 JOIN regions reg ON reg.id = dept.region_id
					WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3) AND ml.men_iddr LIKE '" . $codeDep . "%'
					GROUP BY centdr.COD_DR_2022, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
                    GROUP BY region) col
            ON att.region = col.region ORDER BY att.region
                ";
            }
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        $tabAttendu = [];
        $tabRatio = [];
        $tabCollecte = [];
        $tabReg = [];

        foreach ($stmt as $oneRep) {
            // array_push($tabRetour, [$oneRep['region'], intval($oneRep['attendu']) , intval($oneRep['collecte'])]);
            array_push($tabAttendu, $oneRep['attendu']);
            array_push($tabCollecte, $oneRep['collecte']);
            array_push($tabReg, $oneRep['region']);
            array_push($tabRatio, $oneRep['portion']);
        }

        return new JsonResponse(array('region' => $tabReg, 'attendu' => $tabAttendu, 'collecte' => $tabCollecte, 'portion' => $tabRatio));
    }



    /**
     * 
     * 
     * @Route("/progession_pop/{codeDep}", name="progression_pop",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonProgession($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            
            SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
            COALESCE(ROUND(((CAST(col.collecte AS float) / CAST(att.attendu AS float))*100), 1), 0) AS portion  FROM
            (SELECT region,
                            COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu
                            FROM
                            (SELECT reg.nom AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id] 
                            JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 
                            GROUP BY reg.nom, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                            GROUP BY region) att
            LEFT JOIN
                (	SELECT region,
                            COALESCE(SUM(men_taille_reel), 0) AS collecte FROM
                            (SELECT reg.nom AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id]
                            JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3)
                            GROUP BY reg.nom, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
                            GROUP BY region) col
            ON att.region = col.region ORDER BY att.region
            ";
        } else {
            $requete = "
            
            SELECT att.region, COALESCE(att.attendu, 0) AS attendu, COALESCE(col.collecte,0) AS collecte, 
            COALESCE(ROUND(((CAST(col.collecte AS float) / CAST(att.attendu AS float))*100), 1), 0) AS portion  FROM
            (SELECT region,
                            COALESCE(SUM(COALESCE(men_taille_reel, men_taille)), 0) AS attendu
                            FROM
                            (SELECT reg.nom AS region, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id] 
                            JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 AND reg.code = SUBSTRING('" . $codeDep . "', 1, 2)
                            GROUP BY reg.nom, men_iddr, men_id_edif, men_num, men_taille, men_taille_reel) dstl
                            GROUP BY region) att
            LEFT JOIN
                (	SELECT region,
                            COALESCE(SUM(men_taille_reel), 0) AS collecte FROM
                            (SELECT reg.nom AS region, men_iddr, men_id_edif, men_num, men_taille_reel
                            FROM [menage_cases] mc JOIN [menage_level-1] ml
                            ON mc.id = ml.[case-id] JOIN [menage_menage_rec] mr
                            ON ml.[level-1-id] = mr.[level-1-id]
                            JOIN regions reg ON reg.code = SUBSTRING(ml.men_iddr, 1, 2) WHERE mc.deleted = 0 AND mr.men_etat < 7 AND mr.men_statut IN (2, 3) AND reg.code = SUBSTRING('" . $codeDep . "', 1, 2)
                            GROUP BY reg.nom, men_iddr, men_id_edif, men_num, men_taille_reel) dstl
                            GROUP BY region) col
            ON att.region = col.region ORDER BY att.region
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        $tabDonneesReg = [];
        // $tabAttendu = [];
        // $tabCollecte = [];
        // $tabReg = [];

        foreach ($stmt as $oneRep) {
            array_push($tabDonneesReg, array(
                'region' => $oneRep['region'], 'portion' => $oneRep['portion'], 'collecte' => $oneRep['collecte'], 'attendu' => $oneRep['attendu']
            ));
            // array_push($tabAttendu, $oneRep['attendu']);
            // array_push($tabCollecte, $oneRep['collecte']);
            // array_push($tabReg, $oneRep['region']);
        }

        return new JsonResponse($tabDonneesReg);
    }



    /**
     * 
     * 
     * @Route("/population_par_ages/{codeDep}", name="population_par_age",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonPopulationDesAges($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT reg.nom AS region,
                    SUM(case when cr.ext_b06 = 1 then 1 else 0 end) AS hommes,
                    SUM(case when cr.ext_b06 = 2 then 1 else 0 end) AS femmes
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id]
					JOIN regions reg ON reg.code = SUBSTRING(cl.ext_b0_iddr, 1, 2) WHERE cc.deleted = 0 
					GROUP BY reg.nom ORDER BY region
            ";
        } else {
            $requete = "
            SELECT reg.nom AS region,
            SUM(case when cr.ext_b06 = 1 then 1 else 0 end) AS hommes,
            SUM(case when cr.ext_b06 = 2 then 1 else 0 end) AS femmes
            FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
            ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
            ON cl.[level-1-id] = cr.[level-1-id]
            JOIN regions reg ON reg.code = SUBSTRING(cl.ext_b0_iddr, 1, 2) WHERE cc.deleted = 0 AND reg.code = SUBSTRING('" . $codeDep . "', 1,2)
            GROUP BY reg.nom ORDER BY region
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        $tabAgeHomme = [];
        $tabAgeFemme = [];
        $tabReg = [];

        foreach ($stmt as $oneRep) {
            array_push($tabAgeHomme, $oneRep['hommes']);
            array_push($tabAgeFemme, $oneRep['femmes']);
            array_push($tabReg, $oneRep['region']);
        }

        return new JsonResponse(array('pop_male' => $tabAgeHomme, 'pop_female' => $tabAgeFemme, 'region' => $tabReg));
    }



    /**
     * 
     * 
     * @Route("/rapport_de_masculinite/{codeDep}", name="rapport_masculinite",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonRapportMasculinite($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT for_order, age, SUM(hommes) AS hommes, SUM(femmes) AS femmes, ROUND(((CAST(SUM(hommes) AS float) / CAST(SUM(femmes) AS float))*100), 1) AS ratio  FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS age,
                    SUM(case when cr.ext_b06 = 1 then 1 else 0 end) AS hommes,
                    SUM(case when cr.ext_b06 = 2 then 1 else 0 end) AS femmes
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 GROUP BY cr.ext_b08
            ) Sortie GROUP BY for_order,age ORDER BY for_order
            ";
        } else {
            $requete = "
            SELECT for_order, age, SUM(hommes) AS hommes, SUM(femmes) AS femmes, ROUND(((CAST(SUM(hommes) AS float) / CAST(SUM(femmes) AS float))*100), 1) AS ratio  FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS age,
                    SUM(case when cr.ext_b06 = 1 then 1 else 0 end) AS hommes,
                    SUM(case when cr.ext_b06 = 2 then 1 else 0 end) AS femmes
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 AND cl.ext_b0_iddr LIKE '" . $codeDep . "%' GROUP BY cr.ext_b08
            ) Sortie GROUP BY for_order,age ORDER BY for_order
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        $tab_rap_masc = [];
        // $tabAgeFemme = [];
        // $tabAge = [];
        // $tabRatio = [];

        foreach ($stmt as $oneRep) {
            array_push($tab_rap_masc, array('group_age' => $oneRep['age'], 'ratio' => $oneRep['ratio']));
            // array_push($tabAgeFemme, $oneRep['femmes']);
            // array_push($tabAge, $oneRep['age']);
            // array_push($tabRatio, $oneRep['ratio']);
        }

        return new JsonResponse($tab_rap_masc);
    }


    /**
     * Permet d'afficher les residents par ages'
     * 
     * @Route("/situation_des_residents/{codeDep}", name="residents_par_age",methods={"GET"},options={"expose"=true})
     */

    public function getResidents(
        $codeDep,
        Request $request
    ): Response {

        if ($request->isXmlHttpRequest()) {

            $liste_res = [];

            $liste_res = $this->liste_residents_par_type($codeDep);

            return new JsonResponse($liste_res);
        }
    }


    /**
     * Permet d'afficher les infos de la collecte'
     * 
     * @Route("/Collecte/Informations/{codeDep}", name="donnees_collecte",methods={"GET"},options={"expose"=true})
     */

    public function getInfosCollecte(
        $codeDep,
        Request $request
    ): Response {

        if ($request->isXmlHttpRequest()) {

            $infos_collecte = [];

            $infos_collecte = $this->liste_controle_collecte($codeDep);

            return new JsonResponse($infos_collecte);
        }
    }


    /**
     * @Route("pigor/lancement_collecte", name="lancer_collecte")
     */
    public function LancementCollectePage(
        UserRepository $usersRepo,
        CentroideZsRepository $zsRepo
    ) {

        $filesystem = new Filesystem();
        ini_set('memory_limit', '1024M');

        $ctr = $usersRepo->findOneBy(['id' => $this->getUser()]);

        if ($this->isGranted("ROLE_ADMIN")) {
            $zs = $zsRepo->findBy([], ['codReg' => 'ASC']);
            $sup = $usersRepo->findSuperviseurs("", 'ROLE_SUPERVISEUR');
            $sup_ratissage = $usersRepo->findProfilsOfRegion("", 'ROLE_SUPERVISEUR_RATISSAGE');
        } else if ($this->isGranted("ROLE_CTD")) {
            $zs = $zsRepo->findBy(['codDept' => $ctr->getDepartement()->getCode()]);
            $sup = $usersRepo->findUserByRolesInDepartement('ROLE_SUPERVISEUR', $ctr->getDepartement(), $ctr->getCustomArrnd());
            $sup_ratissage = $usersRepo->findUserByRolesInDepartement('ROLE_SUPERVISEUR_RATISSAGE', $ctr->getDepartement(), $ctr->getCustomArrnd());
        } else if ($this->isGranted("ROLE_CTR")) {
            $zs = $zsRepo->findBy(['codReg' => $ctr->getDepartement()->getRegion()->getCode()]);
            $sup = $usersRepo->findProfilsOfRegion($ctr->getDepartement()->getRegion()->getCode(), 'ROLE_SUPERVISEUR');
            $sup_ratissage = $usersRepo->findProfilsOfRegion($ctr->getDepartement()->getRegion()->getCode(), 'ROLE_SUPERVISEUR_RATISSAGE');
        }

        $supWithClosed = [];
        $sup_r_WithClosed = [];
        foreach ($sup as $oneSup) {
            $closed_file = $this->getParameter('closedFormationSup') . "/closed_" . $oneSup->getEmail() . ".txt";
            $dispachingFile = $this->getParameter('csdbPath') . "/dispaching" . "/dispaching_" . $oneSup->getEmail() . ".csdb";
            $isDispachingFile = $filesystem->exists([$dispachingFile]);

            // var_dump($filesystem->exists([$closed_file])); die;
            array_push($supWithClosed, [
                'super' => $oneSup,
                'isFound' => $filesystem->exists([$closed_file]),
                'isDispatchingFound' => $isDispachingFile,
            ]);
        }
        foreach ($sup_ratissage as $oneSup_r) {
            $dispachingFile = $this->getParameter('csdbPath') . "/dispaching" . "/dispaching_" . $oneSup_r->getEmail() . ".csdb";
            $isDispachingFile = $filesystem->exists([$dispachingFile]);

            // var_dump($filesystem->exists([$closed_file])); die;
            array_push($sup_r_WithClosed, [
                'super' => $oneSup_r,
                'isDispatchingFound' => $isDispachingFile,
            ]);
        }
        return $this->render('completude_collecte/page_lancement_collecte.html.twig', [
            'liste_zs' => $zs,
            'liste_sup' => $supWithClosed,
            'liste_sup_ratissage' => $sup_r_WithClosed,
            'le_ctr' => $ctr,
            'isSuperAdmin' => $this->isGranted("ROLE_ADMIN") ? true : false
        ]);
    }


    /**
     * @Route("pigor/gestion_pcp", name="gestion_pcp")
     */
    public function PcpPage(
        UserRepository $usersRepo,
        CentroideZsRepository $zsRepo,
        CommunePcpRepository $pcpRepo
    ) {

        $filesystem = new Filesystem();
        ini_set('memory_limit', '1024M');

        $ctr = $usersRepo->findOneBy(['id' => $this->getUser()]);

        $codesCacrsArrnd = [];
        if ($ctr->getCustomArrnd()) {
            $homeWorks = $ctr->getCustomArrnd()->getCustomArrondissementCommunes();
            $codesCacrsArrnd =  array_map(function ($cacr) {
                return $cacr->getCacr()->getCode();
            }, $homeWorks->toArray());
        }

        $comPCP = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $comPCP = $pcpRepo->findAll();
        } else if ($ctr->getCustomArrnd() != NUll) {
            $comPCP = $pcpRepo->findCommPCPInArrondissement($codesCacrsArrnd);
        } else {
            $comPCP = $pcpRepo->findCommPCPInDepartement($ctr->getDepartement()->getCode());
        }

        $sup_pcp = $usersRepo->findUserByRolesInDepartement('ROLE_SUPERVISEUR_PCP', $ctr->getDepartement(), $ctr->getCustomArrnd());

        return $this->render('completude_collecte/gestion_pcp.html.twig', [
            'liste_sup_pcp' => $sup_pcp,
            'le_ctr' => $ctr,
            'commAvecPcp' => $comPCP,
        ]);
    }


    public function liste_residents_par_type($codeDep)
    {

        if ($codeDep == 'national') {

            $sql = "
            SELECT for_order, categorie, SUM(presH) AS presH, SUM(absH) AS absH, SUM(visitH) AS visitH
            , SUM(presF) AS presF, SUM(absF) AS absF, SUM(visitF) AS visitF, SUM(total) AS Total FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS categorie,
                    SUM(case when cr.ext_b06 = 1 AND cr.ext_b11 = 1 then 1 else 0 end) AS presH,
                    SUM(case when cr.ext_b06 = 1 AND cr.ext_b11 = 2 then 1 else 0 end) AS absH,
                    SUM(case when cr.ext_b06 = 1 AND cr.ext_b11 = 3 then 1 else 0 end) AS visitH,
                    SUM(case when cr.ext_b06 = 2 AND cr.ext_b11 = 1 then 1 else 0 end) AS presF,
                    SUM(case when cr.ext_b06 = 2 AND cr.ext_b11 = 2 then 1 else 0 end) AS absF,
                    SUM(case when cr.ext_b06 = 2 AND cr.ext_b11 = 3 then 1 else 0 end) AS visitF,
                    SUM(case when cr.ext_b06 IN (1,2) AND cr.ext_b11 IN (1,2,3) then 1 else 0 end) AS total
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 GROUP BY cr.ext_b08
            ) output1 GROUP BY for_order,categorie ORDER BY for_order ASC
            ";
        } else {
            $sql = "
            SELECT for_order, categorie, SUM(presH) AS presH, SUM(absH) AS absH, SUM(visitH) AS visitH
            , SUM(presF) AS presF, SUM(absF) AS absF, SUM(visitF) AS visitF, SUM(total) AS Total FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS categorie,
                    SUM(case when cr.ext_b06 = 1 AND cr.ext_b11 = 1 then 1 else 0 end) AS presH,
                    SUM(case when cr.ext_b06 = 1 AND cr.ext_b11 = 2 then 1 else 0 end) AS absH,
                    SUM(case when cr.ext_b06 = 1 AND cr.ext_b11 = 3 then 1 else 0 end) AS visitH,
                    SUM(case when cr.ext_b06 = 2 AND cr.ext_b11 = 1 then 1 else 0 end) AS presF,
                    SUM(case when cr.ext_b06 = 2 AND cr.ext_b11 = 2 then 1 else 0 end) AS absF,
                    SUM(case when cr.ext_b06 = 2 AND cr.ext_b11 = 3 then 1 else 0 end) AS visitF,
                    SUM(case when cr.ext_b06 IN (1,2) AND cr.ext_b11 IN (1,2,3) then 1 else 0 end) AS total
              FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
              ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
              ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 AND cl.ext_b0_iddr LIKE '" . $codeDep . "%' GROUP BY cr.ext_b08
              ) output1 GROUP BY for_order,categorie ORDER BY for_order ASC
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($sql);

        return  $stmt;
    }



    public function liste_controle_collecte($codeDep)
    {

        if ($codeDep == 'national') {

            $sql = "
            SELECT * FROM
            (SELECT cl.ext_b0_iddr as dr,
                    SUM(case when cr.ext_b11 = 1 then 1 else 0 end) AS pres,
                    SUM(case when cr.ext_b11 = 2 then 1 else 0 end) AS abst,
                    SUM(case when cr.ext_b11 = 3 then 1 else 0 end) AS visiteur,
                    SUM(case when cr.ext_b11 IN (1,2,3) then 1 else 0 end) AS total_resident
            FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
            ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
            ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 GROUP BY cl.ext_b0_iddr
            ) resulta
            LEFT JOIN
            (
            SELECT dr, COUNT(men_id_edif) AS concession, SUM(menage_edif) AS menage FROM (
            SELECT ml.men_iddr as dr, ml.men_id_edif, COUNT(DISTINCT ml.men_num) AS menage_edif
            FROM menage_cases mc JOIN [menage_level-1] ml
            ON mc.id = ml.[case-id] JOIN menage_menage_rec mr
            ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 and mr.men_composition = 1 GROUP BY ml.men_iddr, ml.men_id_edif
            ) for_menage GROUP BY dr ) resultb
            ON resulta.dr = resultb.dr 
            LEFT JOIN
            (
            SELECT dr_coll, COUNT(col_b0_id_edif) AS concession_coll, SUM(menage_edif_coll) AS menage_coll FROM (
            SELECT mcl.col_b0_iddr as dr_coll, mcl.col_b0_id_edif, COUNT(DISTINCT mcl.col_b0_num) AS menage_edif_coll
            FROM collectif_cases mcc JOIN [collectif_level-1] mcl
            ON mcc.id = mcl.[case-id] JOIN collectif_collectif_record mccr
            ON mcl.[level-1-id] = mccr.[level-1-id] WHERE mcc.deleted = 0 GROUP BY mcl.col_b0_iddr, mcl.col_b0_id_edif
            ) for_menage_coll GROUP BY dr_coll ) resultbcoll
            ON resulta.dr = resultbcoll.dr_coll
            LEFT JOIN 
            (
            SELECT reg.nom AS region, dep.nom AS departement, dl.dr_id, (SELECT CONCAT(prenom, ' ', nom) FROM utilisateur WHERE email = ddr.dr_idagent) AS ar, ddr.dr_zc
            , (SELECT CONCAT(prenom, ' ', nom) FROM utilisateur WHERE email = zcdr.zc_idagent) AS controleur
            FROM [dispaching_level-1] dl JOIN dispaching_dispaching_rec ddr 
            ON dl.[level-1-id] = ddr.[level-1-id]
            JOIN [zc_level-1] zcl ON zcl.zc_id = ddr.dr_zc 
            JOIN zc_dispaching_rec zcdr ON zcdr.[level-1-id] = zcl.[level-1-id]
            JOIN departements dep ON zcdr.zc_zc LIKE CONCAT(dep.code, '%')
            JOIN regions reg ON dep.code LIKE CONCAT(reg.code, '%')
            ) resultc
            ON resultb.dr = resultc.dr_id ORDER BY resultb.dr
            ";
        } else {
            $sql = "
            SELECT * FROM
            (SELECT cl.ext_b0_iddr as dr,
                    SUM(case when cr.ext_b11 = 1 then 1 else 0 end) AS pres,
                    SUM(case when cr.ext_b11 = 2 then 1 else 0 end) AS abst,
                    SUM(case when cr.ext_b11 = 3 then 1 else 0 end) AS visiteur,
                    SUM(case when cr.ext_b11 IN (1,2,3) then 1 else 0 end) AS total_resident
            FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
            ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
            ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 and cl.ext_b0_iddr LIKE '" . $codeDep . "%' GROUP BY cl.ext_b0_iddr
            ) resulta
            LEFT JOIN
            (
            SELECT dr, COUNT(men_id_edif) AS concession, SUM(menage_edif) AS menage FROM (
            SELECT ml.men_iddr as dr, ml.men_id_edif, COUNT(DISTINCT ml.men_num) AS menage_edif
            FROM menage_cases mc JOIN [menage_level-1] ml
            ON mc.id = ml.[case-id] JOIN menage_menage_rec mr
            ON ml.[level-1-id] = mr.[level-1-id] WHERE mc.deleted = 0 and mr.men_composition = 1 and ml.men_iddr LIKE '" . $codeDep . "%' GROUP BY ml.men_iddr, ml.men_id_edif
            ) for_menage GROUP BY dr ) resultb
            ON resulta.dr = resultb.dr 
            LEFT JOIN
            (
            SELECT dr_coll, COUNT(col_b0_id_edif) AS concession_coll, SUM(menage_edif_coll) AS menage_coll FROM (
            SELECT mcl.col_b0_iddr as dr_coll, mcl.col_b0_id_edif, COUNT(DISTINCT mcl.col_b0_num) AS menage_edif_coll
            FROM collectif_cases mcc JOIN [collectif_level-1] mcl
            ON mcc.id = mcl.[case-id] JOIN collectif_collectif_record mccr
            ON mcl.[level-1-id] = mccr.[level-1-id] WHERE mcc.deleted = 0 and mcl.col_b0_iddr LIKE '" . $codeDep . "%' GROUP BY mcl.col_b0_iddr, mcl.col_b0_id_edif
            ) for_menage_coll GROUP BY dr_coll ) resultbcoll
            ON resulta.dr = resultbcoll.dr_coll
            LEFT JOIN 
            (
            SELECT reg.nom AS region, dep.nom AS departement, dl.dr_id, (SELECT CONCAT(prenom, ' ', nom) FROM utilisateur WHERE email = ddr.dr_idagent) AS ar, ddr.dr_zc
            , (SELECT CONCAT(prenom, ' ', nom) FROM utilisateur WHERE email = zcdr.zc_idagent) AS controleur
            FROM [dispaching_level-1] dl JOIN dispaching_dispaching_rec ddr 
            ON dl.[level-1-id] = ddr.[level-1-id]
            JOIN [zc_level-1] zcl ON zcl.zc_id = ddr.dr_zc 
            JOIN zc_dispaching_rec zcdr ON zcdr.[level-1-id] = zcl.[level-1-id]
            JOIN departements dep ON zcdr.zc_zc LIKE CONCAT(dep.code, '%')
            JOIN regions reg ON dep.code LIKE CONCAT(reg.code, '%') WHERE dl.dr_id LIKE '" . $codeDep . "%'
            ) resultc
            ON resultb.dr = resultc.dr_id ORDER BY resultb.dr
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($sql);

        return  $stmt;
    }

    /**
     * 
     * 
     * @Route("/population_par_agent/{codeDep}", name="population_par_agent",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonPopulationAgent($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " AND cl.ext_b0_iddr LIKE '" . $codeDep . "%'";

        $requete = "
            SELECT cl.ext_b0_iddr AS codedr,com.nom AS commune,CONCAT( u.prenom,' ', u.nom) AS agent , u.id, COUNT(*) as nbindividu 
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] 
                    JOIN [dispaching_level-1] dl  ON dl.[dr_id] = cl.[ext_b0_iddr]
                    JOIN [dispaching_dispaching_rec] dr  ON dr.[level-1-id] = dl.[level-1-id]
                    JOIN [utilisateur]  u ON u.[email] = dr.[dr_idagent]
                    JOIN [departements] dep ON dep.[id] = u.[departement_id]
                    LEFT JOIN [communes_arr_communautes_rurales]  com ON com.[id] = u.[commune_id] WHERE cc.deleted = 0 AND cr.ext_ind_status = 0 $code
					GROUP BY  cl.ext_b0_iddr, com.nom, u.prenom, u.nom, u.id ORDER BY cl.ext_b0_iddr
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        return new JsonResponse($stmt);
    }

    /**
     * 
     * 
     * @Route("/performance_agent_menage/{codeDep}", name="performance_agent_menage",methods={"GET"},options={"expose"=true})
     *  mr.men_etat < 7 AND mr.men_statut IN (2, 3)
     */
    public function JsonPerformanceAgentM($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " WHERE ml.[men_iddr] LIKE '" . $codeDep . "%'";

        $requete = "
            SELECT  ml.[men_iddr] AS codedr, com.nom AS commune,CONCAT( us.prenom, us.nom) AS agent ,us.id,
                    SUM(case when mr.men_etat < 7 AND mr.men_statut IN (2, 3) then mr.men_taille_reel else 0 end) AS popreel, 
                    SUM(mr.men_taille) AS popattend,
                    COALESCE(ROUND(((CAST(SUM(case when mr.men_etat < 7 AND mr.men_statut IN (2, 3) then mr.men_taille_reel else 0 end) AS float) / CAST(SUM(mr.men_taille) AS float))*100), 2), 0) AS prc_pop,
                    COUNT(case when mr.men_etat < 7 AND mr.men_statut IN (2, 3) then 1 else 0 end) as menagedenomb,
                    COUNT(mr.[level-1-id]) as menageattend,
                    COALESCE(ROUND(((CAST(COUNT(CASE WHEN mr.men_etat < 7 AND mr.men_statut IN (2, 3) then 1 else 0 end) AS float) / CAST(COUNT(mr.[level-1-id]) AS float))*100), 2), 0) AS prc_men
                    FROM [menage_menage_rec] mr JOIN [menage_level-1] ml
                    ON mr.[level-1-id] = ml.[level-1-id]
                    JOIN [dispaching_level-1] dl  ON dl.[dr_id] = ml.[men_iddr]
                    JOIN [dispaching_dispaching_rec] dr  ON dr.[level-1-id] = dl.[level-1-id]
                    JOIN [utilisateur]  u ON u.[email] = dr.[dr_idagent]
                    JOIN [composition]  c ON c.[arr_id] = u.[id]
                    JOIN [utilisateur]  us ON us.[id] = c.[superviseur_id]
                    LEFT JOIN [communes_arr_communautes_rurales]  com ON com.[id] = u.[commune_id] $code
					GROUP BY  ml.[men_iddr], com.nom , us.prenom, us.nom, us.id ORDER BY ml.[men_iddr]
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        return  new JsonResponse($stmt);
    }

    /**
     * 
     * 
     * @Route("/performance_sup_menage/{codeDep}", name="performance_sup_menage",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonPerformanceSupM($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " WHERE ml.[men_iddr] LIKE '" . $codeDep . "%'";

        $requete = "
            SELECT  zs.[COD_ZSUP] AS codezs,CONCAT( us.prenom,' ', us.nom) AS agent ,us.id,
            SUM(case when mr.men_etat < 7 AND mr.men_statut IN (2, 3) then mr.men_taille_reel else 0 end) AS popreel, 
            SUM(mr.men_taille) AS popattend,
            COALESCE(ROUND(((CAST(SUM(case when mr.men_etat < 7 AND mr.men_statut IN (2, 3) then mr.men_taille_reel else 0 end) AS float) / CAST(SUM(mr.men_taille) AS float))*100), 2), 0) AS prc_pop,
            COUNT(CASE WHEN mr.men_etat < 7 AND mr.men_statut IN (2, 3) then 1 else 0 end) as menagedenomb,
            COUNT(mr.[level-1-id]) as menageattend,
            COALESCE(ROUND(((CAST(COUNT(CASE WHEN mr.men_etat < 7 AND mr.men_statut IN (2, 3) then 1 else 0 end) AS float) / CAST(COUNT(mr.[level-1-id]) AS float))*100), 2), 0) AS prc_men
            FROM [menage_menage_rec] mr JOIN [menage_level-1] ml
            ON mr.[level-1-id] = ml.[level-1-id]
            JOIN [dispaching_level-1] dl  ON dl.[dr_id] = ml.[men_iddr]
            JOIN [dispaching_dispaching_rec] dr  ON dr.[level-1-id] = dl.[level-1-id]
            JOIN [utilisateur]  u ON u.[email] = dr.[dr_idagent]
            JOIN [composition]  c ON c.[arr_id] = u.[id]
            JOIN [utilisateur]  us ON us.[id] = c.[superviseur_id]
            JOIN [CENTROIDE_ZS] zs ON zs.[superviseur_id]= us.id $code
            GROUP BY  zs.COD_ZSUP, us.prenom, us.nom, us.id ORDER BY zs.COD_ZSUP
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        return  new JsonResponse($stmt);
    }

    /**
     * 
     * 
     * @Route("/performance_dr_dece/{codeDep}", name="dr_dece_menage",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonDeceM($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " AND ml.men_iddr LIKE '" . $codeDep . "%'";

        $requete = "
            SELECT  ml.[men_iddr] AS codedr,cm.nom AS commune , CONCAT( u.prenom,' ', u.nom) AS agent ,CONCAT( us.prenom, us.nom) AS superviseur ,
                    COUNT(*) as menagedenomb
                    FROM [menage_menage_rec] mr JOIN [menage_level-1] ml
                    ON mr.[level-1-id] = ml.[level-1-id]
                    JOIN [dispaching_level-1] dl  ON dl.[dr_id] = ml.[men_iddr]
                    JOIN [dispaching_dispaching_rec] dr  ON dr.[level-1-id] = dl.[level-1-id]
                    JOIN [utilisateur]  u ON u.[email] = dr.[dr_idagent]
                    JOIN [composition]  c ON c.[arr_id] = u.[id]
                    LEFT JOIN [communes_arr_communautes_rurales]  cm ON cm.[id] = u.[commune_id]
                    JOIN [utilisateur]  us ON us.[id] = c.[superviseur_id] 
					WHERE mr.is_deces IS NOT NULL $code
					GROUP BY  ml.[men_iddr], u.prenom,cm.nom, u.nom, us.prenom, us.nom HAVING
                    COUNT(*) = 0 ORDER BY ml.[men_iddr]
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        return  new JsonResponse($stmt);
    }

    /**
     * 
     * 
     * @Route("/naissance_non_dr/{codeDep}", name="non_naissance_dr",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonNaissDr($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " cl.ext_b0_iddr LIKE '" . $codeDep . "%' AND ";

        $requete = "
            SELECT cl.ext_b0_iddr AS codedr,com.nom AS commune,CONCAT( u.prenom,' ', u.nom) AS agent , CONCAT( us.prenom,' ', us.nom) AS superviseur , COUNT(*) as nbindividu 
            FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
            ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
            ON cl.[level-1-id] = cr.[level-1-id]
            JOIN [dispaching_level-1] dl  ON dl.[dr_id] = cl.[ext_b0_iddr]
            JOIN [dispaching_dispaching_rec] dr  ON dr.[level-1-id] = dl.[level-1-id]
            JOIN [utilisateur]  u ON u.[email] = dr.[dr_idagent]
            JOIN [departements] dep ON dep.[id] = u.[departement_id]
            JOIN [composition] c ON c.[arr_id]= u.[id]
            JOIN [utilisateur]  us ON us.[id] = c.[superviseur_id]  
            LEFT JOIN [communes_arr_communautes_rurales]  com ON com.[id] = u.[commune_id] 
            WHERE  $code cl.ext_b0_iddr NOT IN (
                SELECT l.ext_b0_iddr
                FROM ext_composition_cases c JOIN [ext_composition_level-1] l ON c.id = l.[case-id]
                JOIN ext_composition_ext_composition_rec r ON r.[level-1-id] = l.[level-1-id] 
                WHERE c.deleted = 0 AND r.ext_b08 = 0 GROUP BY l.ext_b0_iddr
                ) 
            GROUP BY  cl.ext_b0_iddr,com.nom , u.prenom, u.nom , us.prenom, us.nom 
            ORDER BY cl.ext_b0_iddr
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        return  new JsonResponse($stmt);
    }


    /**
     * 
     * 
     * @Route("/getRepportDR/{codeDep}", name="get_repport_tab",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonGetReports($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " AND cl.ext_b0_iddr LIKE '" . $codeDep . "%'";


        $requete = "
                    SELECT COUNT(*) AS nbindividu 
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl
                    ON cc.id = cl.[case-id] JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 AND cr.ext_ind_status = 0 $code           
                ";

        $stmt = $this->conn->fetchAllAssociative($requete);
        $oneRep = $stmt[0];

        $coden = " AND ml.[men_iddr] LIKE '" . $codeDep . "%'";
        $reqDouble = "
                    SELECT  ml.[men_iddr] ,
                    COUNT(*) as menagedenomb
                    FROM [menage_menage_rec] mr JOIN [menage_level-1] ml
                    ON mr.[level-1-id] = ml.[level-1-id]
                    WHERE mr.is_deces IS NOT NULL $coden
                    GROUP BY  ml.[men_iddr] HAVING
                    COUNT(*) = 0 
            ";


        $stmtD = $this->conn->fetchAllAssociative($reqDouble);
        $DeceRep = count($stmtD);


        $reqNaiss = "
            SELECT cl.[ext_b0_iddr] , COUNT(*) as nbindividu 
            FROM  [ext_composition_level-1] cl
            WHERE  cl.[ext_b0_iddr] NOT IN (
                SELECT l.ext_b0_iddr
                FROM ext_composition_cases c JOIN [ext_composition_level-1] l ON c.id = l.[case-id]
                JOIN ext_composition_ext_composition_rec r ON r.[level-1-id] = l.[level-1-id] 
                WHERE c.deleted = 0 AND r.ext_b08 = 0 GROUP BY l.ext_b0_iddr
            )  $code                    	
            GROUP BY cl.[ext_b0_iddr]";

        $stmtD = $this->conn->fetchAllAssociative($reqNaiss);
        $NaissRep = count($stmtD);


        return new JsonResponse(array('total' => $oneRep['nbindividu'],  'complet' => $DeceRep, 'doublon' => $NaissRep));
    }


    /**
     * @Route("pigor/tableau_de_bord_indic", name="dashboard_indic")
     */
    public function dashboardPageIndic(
        Request $request,
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
        DepartementsRepository $deptRepo,
        CommunesArrCommunautesRuralesRepository $comRepo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ) {

        ini_set('memory_limit', '1024M');

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN') || $this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD') || $this->isGranted('ROLE_CTD')) {
            // $breadcrumbs->addRouteItem("Tableau de bord", 'dashboard_pigor_page2');
        }
        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $monCodeDepartement = ($me->getDepartement() != NULL) ? $me->getDepartement()->getCode() : NULL;


        $departements = [];
        $communesAr = [];

        if ($this->isGranted('ROLE_CTR') || $this->isGranted('ROLE_SRSD')) {
            $codReg = substr($me->getDepartement()->getCode(), 0, 2);
            $departements = $deptRepo->findBy(["codeParent" => $codReg], ['code' => "ASC"]);
        } elseif ($this->isGranted('ROLE_CTD')) {
            $codDep = $me->getDepartement()->getCode();
            $communesAr = $comRepo->findDeptCacrs($codDep);
        }

        return $this->render('completude_collecte/dashboard_indic.html.twig', [
            'connectedUser' => $me,
            'regions' => $regionsRepository->findBy([], ['code' => 'ASC']),
            'departements' => $departements,
            'communesAr' => $communesAr,
        ]);
    }



    /**
     * 
     * 
     * @Route("/population_par_age_simple/{codeDep}", name="population_par_age_simple",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonPopulationAge($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " AND cl.ext_b0_iddr LIKE '" . $codeDep . "%' ";

        $requete = "
            SELECT CAST(cr.ext_b08 AS float) AS age , COUNT(*) as nbindividu , 
					COALESCE(ROUND(((CAST(COUNT(*) AS float) / CAST(
					  (SELECT COUNT(*) FROM  [ext_composition_cases] cc JOIN [ext_composition_level-1] cl ON cc.[id] = cl.[case-id]
                        JOIN [ext_composition_ext_composition_rec] cr
                        ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 $code) AS float))*100), 1), 0) AS prc_pop

                    FROM  [ext_composition_cases] cc JOIN [ext_composition_level-1] cl ON cc.[id] = cl.[case-id]
                     JOIN [ext_composition_ext_composition_rec] cr
                    ON cl.[level-1-id] = cr.[level-1-id]  
                    WHERE cc.deleted = 0 $code
                    GROUP BY  cr.ext_b08 ORDER BY cr.ext_b08
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        return new JsonResponse($stmt);
    }


    /**
     * 
     * 
     * @Route("/population_par_tranche_age/{codeDep}", name="population_tranche_age",methods={"GET"},options={"expose"=true})
     *
     */
    public function JsonPopulationTA($codeDep)
    {
        $code = "";
        if ($codeDep != 'national')
            $code = " AND cl.ext_b0_iddr LIKE '" . $codeDep . "%'";

        $requete = "SELECT for_order, age, COUNT(*) AS ratio ,COALESCE(ROUND(((CAST(COUNT(*) AS float) / CAST(
                (SELECT COUNT(*) FROM  [ext_composition_cases] cc JOIN [ext_composition_level-1] cl ON cc.[id] = cl.[case-id]
                  JOIN [ext_composition_ext_composition_rec] cr
                  ON cl.[level-1-id] = cr.[level-1-id] WHERE cc.deleted = 0 $code) AS float))*100), 2), 0) AS prc_pop
            
            FROM
            (
            SELECT CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN 'a' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN 'b' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN 'c' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN 'd' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN 'e' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN 'f' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN 'g' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN 'h' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN 'i' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN 'j' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN 'k' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN 'l' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN 'm' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN 'n' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN 'o' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN 'p' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN 'q' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN 'r' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN 's' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN 't' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN 'u' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN 'v' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN 'w' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN 'x' 
                    END AS for_order,
                CASE 
                    WHEN cr.ext_b08 BETWEEN 0 AND 4 THEN '0-4' 
                    WHEN cr.ext_b08 BETWEEN 5 AND 9 THEN '5-9' 
                    WHEN cr.ext_b08 BETWEEN 10 AND 14 THEN '10-14' 
                    WHEN cr.ext_b08 BETWEEN 15 AND 19 THEN '15-19' 
                    WHEN cr.ext_b08 BETWEEN 20 AND 24 THEN '20-24' 
                    WHEN cr.ext_b08 BETWEEN 25 AND 29 THEN '25-29' 
                    WHEN cr.ext_b08 BETWEEN 30 AND 34 THEN '30-34' 
                    WHEN cr.ext_b08 BETWEEN 35 AND 39 THEN '35-39' 
                    WHEN cr.ext_b08 BETWEEN 40 AND 44 THEN '40-44' 
                    WHEN cr.ext_b08 BETWEEN 45 AND 49 THEN '45-49' 
                    WHEN cr.ext_b08 BETWEEN 50 AND 54 THEN '50-54' 
                    WHEN cr.ext_b08 BETWEEN 55 AND 59 THEN '55-59' 
                    WHEN cr.ext_b08 BETWEEN 60 AND 64 THEN '60-64' 
                    WHEN cr.ext_b08 BETWEEN 65 AND 69 THEN '65-69' 
                    WHEN cr.ext_b08 BETWEEN 70 AND 74 THEN '70-74' 
                    WHEN cr.ext_b08 BETWEEN 75 AND 79 THEN '75-79' 
                    WHEN cr.ext_b08 BETWEEN 80 AND 84 THEN '80-84' 
                    WHEN cr.ext_b08 BETWEEN 85 AND 89 THEN '85-89' 
                    WHEN cr.ext_b08 BETWEEN 90 AND 94 THEN '90-94' 
                    WHEN cr.ext_b08 BETWEEN 95 AND 99 THEN '95-99' 
                    WHEN cr.ext_b08 BETWEEN 100 AND 104 THEN '100-104' 
                    WHEN cr.ext_b08 BETWEEN 105 AND 109 THEN '105-109' 
                    WHEN cr.ext_b08 BETWEEN 110 AND 114 THEN '110-114' 
                    WHEN cr.ext_b08 BETWEEN 115 AND 119 THEN '115-119' 
                    END AS age
                    FROM [ext_composition_cases] cc JOIN [ext_composition_level-1] cl ON cc.[id] = cl.[case-id]  
                    JOIN [ext_composition_ext_composition_rec] cr ON cl.[level-1-id] = cr.[level-1-id]
                    WHERE cc.deleted = 0 $code
            ) Sortie  GROUP BY for_order, age ORDER BY for_order
            ";


        $stmt = $this->conn->fetchAllAssociative($requete);

        $tab_rap_masc = [];


        foreach ($stmt as $oneRep) {
            array_push($tab_rap_masc, array('group_age' => $oneRep['age'], 'ratio' => $oneRep['ratio'], 'prcpop' => $oneRep['prc_pop']));
        }

        return new JsonResponse($tab_rap_masc);
    }



    /**
     * 
     * 
     * @Route("/suivi_remontee_superviseur/{codeDep}", name="suivi_rem_sup",methods={"GET"},options={"expose"=true})
     *
     */
    public function json_suivi_rem_sup($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT llev.user_login AS username, lrec.user_firstname AS prenom, lrec.user_lastname AS nom, CONCAT(llev.user_login, '|', lrec.user_firstname, ' ', lrec.user_lastname ) AS superviseur,dep.nom AS departement,
            lrec.user_lastconnect AS date_dern_remontee, convert(varchar(10), cast(convert(varchar(10), lrec.user_lastconnect) AS date),103) AS formatDate, lrec.sync_controle_count AS nb_sync,
            DATEDIFF(DAY, CAST(convert(varchar(10), lrec.user_lastconnect) as DATETIME), GETDATE()) AS nb_jour, lrec.user_zone AS zone_sup
            FROM ext_login_cases lcase JOIN [ext_login_level-1] llev ON lcase.id = llev.[case-id]
            JOIN ext_login_ext_log_rec lrec ON llev.[level-1-id] = lrec.[level-1-id] 
            JOIN departements dep ON dep.code = lrec.user_department
            WHERE lcase.deleted = 0 AND lrec.sync_controle_count IS NOT NULL AND llev.user_login LIKE 'SP%'
            ORDER BY nb_jour DESC
            ";
        } else {
            if (strlen($codeDep) > 2) {
                $requete = "
                SELECT llev.user_login AS username, lrec.user_firstname AS prenom, lrec.user_lastname AS nom, CONCAT(llev.user_login, '|', lrec.user_firstname, ' ', lrec.user_lastname ) AS superviseur,dep.nom AS departement,
                lrec.user_lastconnect AS date_dern_remontee, convert(varchar(10), cast(convert(varchar(10), lrec.user_lastconnect) AS date),103) AS formatDate, lrec.sync_controle_count AS nb_sync,
                DATEDIFF(DAY, CAST(convert(varchar(10), lrec.user_lastconnect) as DATETIME), GETDATE()) AS nb_jour, lrec.user_zone AS zone_sup
                FROM ext_login_cases lcase JOIN [ext_login_level-1] llev ON lcase.id = llev.[case-id]
                JOIN ext_login_ext_log_rec lrec ON llev.[level-1-id] = lrec.[level-1-id] 
                JOIN departements dep ON dep.code = lrec.user_department
                WHERE lcase.deleted = 0 AND lrec.sync_controle_count IS NOT NULL AND llev.user_login LIKE 'SP%' AND SUBSTRING('" . $codeDep . "', 1, 3) =  lrec.user_department
                ORDER BY nb_jour DESC
                ";
            } else {
                $requete = "
                SELECT llev.user_login AS username, lrec.user_firstname AS prenom, lrec.user_lastname AS nom, CONCAT(llev.user_login, '|', lrec.user_firstname, ' ', lrec.user_lastname ) AS superviseur,dep.nom AS departement,
                lrec.user_lastconnect AS date_dern_remontee, convert(varchar(10), cast(convert(varchar(10), lrec.user_lastconnect) AS date),103) AS formatDate, lrec.sync_controle_count AS nb_sync,
                DATEDIFF(DAY, CAST(convert(varchar(10), lrec.user_lastconnect) as DATETIME), GETDATE()) AS nb_jour, lrec.user_zone AS zone_sup
                FROM ext_login_cases lcase JOIN [ext_login_level-1] llev ON lcase.id = llev.[case-id]
                JOIN ext_login_ext_log_rec lrec ON llev.[level-1-id] = lrec.[level-1-id] 
                JOIN departements dep ON dep.code = lrec.user_department
                WHERE lcase.deleted = 0 AND lrec.sync_controle_count IS NOT NULL AND llev.user_login LIKE 'SP%' AND lrec.user_department LIKE '" . $codeDep . "%' 
                ORDER BY nb_jour DESC
                ";
            }
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        return new JsonResponse($stmt);
    }



    /**
     * 
     * 
     * @Route("/suivi_remontee_agent_recenseur/{codeDep}", name="suivi_rem_ar",methods={"GET"},options={"expose"=true})
     *
     */
    public function json_suivi_rem_ar($codeDep)
    {

        if ($codeDep == 'national') {
            $requete = "
            SELECT llev.user_login AS username, lrec.user_firstname AS prenom, lrec.user_lastname AS nom, CONCAT(llev.user_login, '|', lrec.user_firstname, ' ', lrec.user_lastname ) AS agent, 
            dlev.dr_id AS dr_travail, dep.nom AS departement,
                lrec.user_lastconnect AS date_dern_remontee, convert(varchar(10), cast(convert(varchar(10), lrec.user_lastconnect) AS date),103) AS formatDate, lrec.sync_controle_count AS nb_sync,
                DATEDIFF(DAY, CAST(convert(varchar(10), lrec.user_lastconnect) as DATETIME), GETDATE()) AS nb_jour, lrec.user_zone AS zone_sup
                FROM ext_login_cases lcase JOIN [ext_login_level-1] llev ON lcase.id = llev.[case-id]
                JOIN ext_login_ext_log_rec lrec ON llev.[level-1-id] = lrec.[level-1-id] 
                JOIN departements dep ON dep.code = lrec.user_department
                JOIN dispaching_dispaching_rec drec ON drec.dr_idagent = llev.user_login
                JOIN [dispaching_level-1] dlev ON dlev.[level-1-id] = drec.[level-1-id]
                WHERE lcase.deleted = 0 AND lrec.sync_controle_count IS NOT NULL AND llev.user_login NOT LIKE 'SP%'
            ORDER BY nb_jour DESC
            ";
        } else {
            if (strlen($codeDep) > 2) {
                $requete = "
                SELECT llev.user_login AS username, lrec.user_firstname AS prenom, lrec.user_lastname AS nom, CONCAT(llev.user_login, '|', lrec.user_firstname, ' ', lrec.user_lastname ) AS agent, 
                dlev.dr_id AS dr_travail, dep.nom AS departement,
                lrec.user_lastconnect AS date_dern_remontee, convert(varchar(10), cast(convert(varchar(10), lrec.user_lastconnect) AS date),103) AS formatDate, lrec.sync_controle_count AS nb_sync,
                DATEDIFF(DAY, CAST(convert(varchar(10), lrec.user_lastconnect) as DATETIME), GETDATE()) AS nb_jour, lrec.user_zone AS zone_sup
                FROM ext_login_cases lcase JOIN [ext_login_level-1] llev ON lcase.id = llev.[case-id]
                JOIN ext_login_ext_log_rec lrec ON llev.[level-1-id] = lrec.[level-1-id] 
                JOIN departements dep ON dep.code = lrec.user_department
                JOIN dispaching_dispaching_rec drec ON drec.dr_idagent = llev.user_login
                JOIN [dispaching_level-1] dlev ON dlev.[level-1-id] = drec.[level-1-id]
                WHERE lcase.deleted = 0 AND lrec.sync_controle_count IS NOT NULL AND llev.user_login NOT LIKE 'SP%' AND SUBSTRING('" . $codeDep . "', 1, 3) =  lrec.user_department
                ORDER BY nb_jour DESC
                ";
            } else {
                $requete = "
                SELECT llev.user_login AS username, lrec.user_firstname AS prenom, lrec.user_lastname AS nom, CONCAT(llev.user_login, '|', lrec.user_firstname, ' ', lrec.user_lastname ) AS agent, 
                dlev.dr_id AS dr_travail, dep.nom AS departement,
                    lrec.user_lastconnect AS date_dern_remontee, convert(varchar(10), cast(convert(varchar(10), lrec.user_lastconnect) AS date),103) AS formatDate, lrec.sync_controle_count AS nb_sync,
                    DATEDIFF(DAY, CAST(convert(varchar(10), lrec.user_lastconnect) as DATETIME), GETDATE()) AS nb_jour, lrec.user_zone AS zone_sup
                    FROM ext_login_cases lcase JOIN [ext_login_level-1] llev ON lcase.id = llev.[case-id]
                    JOIN ext_login_ext_log_rec lrec ON llev.[level-1-id] = lrec.[level-1-id] 
                    JOIN departements dep ON dep.code = lrec.user_department
                    JOIN dispaching_dispaching_rec drec ON drec.dr_idagent = llev.user_login
                    JOIN [dispaching_level-1] dlev ON dlev.[level-1-id] = drec.[level-1-id]
                    WHERE lcase.deleted = 0 AND lrec.sync_controle_count IS NOT NULL AND llev.user_login NOT LIKE 'SP%' AND lrec.user_department LIKE '" . $codeDep . "%' 
                ORDER BY nb_jour DESC
                ";
            }
        }

        $stmt = $this->conn->fetchAllAssociative($requete);

        return new JsonResponse($stmt);
    }



    /**
     * Permet d'exporter la liste des ménages non entame
     * 
     * @Route("/pigor/export/menages_non_entame/{codeDep}", name="export_menages_nonentame",  options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @throws \Exception
     */
    public function exportMenNonEntameExcel(
        Request $request,
        DepartementsRepository $depRepository,
        $codeDep
    ) {
        $spreadsheet = new Spreadsheet();
        // initialiser les premières colonnes
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'CODE DR');
        $sheet->setCellValue('B1', 'ID EDIFICE');
        $sheet->setCellValue('C1', 'NUM MENAGE');
        $sheet->setCellValue('D1', 'CM');
        $sheet->setCellValue('E1', 'TAILLE');
        $sheet->setCellValue('F1', 'TAILLE REELLE');
        $sheet->setCellValue('G1', 'TEL');
        $sheet->setCellValue('H1', 'STATUT');
        $sheet->setCellValue('I1', 'ETAT');
        $sheet->setCellValue('J1', 'ADRESSE');

        $spreadsheet->getActiveSheet()->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'e9e8ea'
                ]
            ],
            'font' => [
                'bold' => true
            ]
        ]);


        $writer = new Xlsx($spreadsheet);

        // incrémente le compteur des éléments non enregistrés 
        $nextRow = 1;

        $menagesEnDoubles = $this->getListeMenNonEntame($codeDep);
        // var_dump($menagesEnDoubles); die;

        try {
            foreach ($menagesEnDoubles as $q) {
                $nextRow++;
                // insert les données
                $sheet->setCellValue('A' . $nextRow, $q['men_iddr']);
                $sheet->setCellValue('B' . $nextRow, $q['men_id_edif']);
                $sheet->setCellValue('C' . $nextRow, $q['men_num']);
                $sheet->setCellValue('D' . $nextRow, $q['men_cm']);
                $sheet->setCellValue('E' . $nextRow, $q['men_taille']);
                $sheet->setCellValue('F' . $nextRow, $q['men_taille_reel']);
                $sheet->setCellValue('G' . $nextRow, $q['men_tel']);
                $sheet->setCellValue('H' . $nextRow, $q['men_statut']);
                $sheet->setCellValue('I' . $nextRow, $q['men_etat']);
                $sheet->setCellValue('J' . $nextRow, $q['men_adress']);

                // 

            }
        } catch (\Throwable $th) {
            throw $th;
        }

        //$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        //$spreadsheet->getActiveSheet()->getStyle('A:H')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

        $fileName = 'menagesNonEntames.xlsx';
        try {
            $dep = $depRepository->findOneBy(['code' => $codeDep]);
            if ($dep) {
                $fileName = 'Menages_non_entames_' . $dep->getNom() . '_' . $codeDep . '.xlsx';
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }



    /**
     * Permet d'exporter la liste des ménages en double
     * 
     * @Route("/pigor/export/menages_double/{codeDep}", name="export_menages_double",  options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @throws \Exception
     */
    public function exportMenDoubleExcel(
        Request $request,
        DepartementsRepository $depRepository,
        $codeDep
    ) {
        $spreadsheet = new Spreadsheet();
        // initialiser les premières colonnes
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'CODE DR');
        $sheet->setCellValue('B1', 'ID EDIFICE');
        $sheet->setCellValue('C1', 'NUM MENAGE');
        $sheet->setCellValue('D1', 'CM');
        $sheet->setCellValue('E1', 'TAILLE');
        $sheet->setCellValue('F1', 'TAILLE REELLE');
        $sheet->setCellValue('G1', 'TEL');
        $sheet->setCellValue('H1', 'STATUT');
        $sheet->setCellValue('I1', 'ETAT');
        $sheet->setCellValue('J1', 'ADRESSE');

        $spreadsheet->getActiveSheet()->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'e9e8ea'
                ]
            ],
            'font' => [
                'bold' => true
            ]
        ]);


        $writer = new Xlsx($spreadsheet);

        // incrémente le compteur des éléments non enregistrés 
        $nextRow = 1;

        $menagesEnDoubles = $this->getListeMenDouble($codeDep);
        // var_dump($menagesEnDoubles); die;

        try {
            foreach ($menagesEnDoubles as $q) {
                $nextRow++;
                // insert les données
                $sheet->setCellValue('A' . $nextRow, $q['men_iddr']);
                $sheet->setCellValue('B' . $nextRow, $q['men_id_edif']);
                $sheet->setCellValue('C' . $nextRow, $q['men_num']);
                $sheet->setCellValue('D' . $nextRow, $q['men_cm']);
                $sheet->setCellValue('E' . $nextRow, $q['men_taille']);
                $sheet->setCellValue('F' . $nextRow, $q['men_taille_reel']);
                $sheet->setCellValue('G' . $nextRow, $q['men_tel']);
                $sheet->setCellValue('H' . $nextRow, $q['men_statut']);
                $sheet->setCellValue('I' . $nextRow, $q['men_etat']);
                $sheet->setCellValue('J' . $nextRow, $q['men_adress']);

                // 

            }
        } catch (\Throwable $th) {
            throw $th;
        }

        //$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        //$spreadsheet->getActiveSheet()->getStyle('A:H')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

        $fileName = 'menagesEnDoubles.xlsx';
        try {
            $dep = $depRepository->findOneBy(['code' => $codeDep]);
            if ($dep) {
                $fileName = 'Menages_en_double_' . $dep->getNom() . '_' . $codeDep . '.xlsx';
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }



    public function getListeMenDouble($codeDep)
    {

        if ($codeDep == 'national') {

            $reqDouble = "
            SELECT down.*, up.* FROM
                (SELECT men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num, men_rec.*
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 ) down
				JOIN
				(SELECT * FROM (
                SELECT men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num,COUNT(*) AS presence
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 GROUP BY men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num) Isdouble
                WHERE presence > 1) up
				ON down.men_iddr = up.men_iddr AND down.men_id_edif = up.men_id_edif AND down.men_num = up.men_num
				ORDER BY down.men_iddr, down.men_id_edif, down.men_num
                ";
        } else {

            $reqDouble = "
            SELECT down.*, up.* FROM
                (SELECT men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num, men_rec.*
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 AND men_lev.men_iddr LIKE '" . $codeDep . "%') down
				JOIN
				(SELECT * FROM (
                SELECT men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num,COUNT(*) AS presence
                FROM [menage_cases] men_case
                JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
                JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
                WHERE men_case.deleted = 0 AND men_rec.men_etat < 7 GROUP BY men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num) Isdouble
                WHERE presence > 1) up
				ON down.men_iddr = up.men_iddr AND down.men_id_edif = up.men_id_edif AND down.men_num = up.men_num
				ORDER BY down.men_iddr, down.men_id_edif, down.men_num
                ";
        }

        $stmt = $this->conn->fetchAllAssociative($reqDouble);
        return $stmt;

        // $stmt = $this->conn->prepare($reqDouble);
        // $stmt->execute();

        // return $stmt->fetchAll();
    }


    public function getListeMenNonEntame($codeDep)
    {

        if ($codeDep == 'national') {

            $reqNonEntame = "
            SELECT men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num, men_rec.*
            FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0 AND men_rec.men_statut = 1 AND men_rec.men_etat < 7
                ";
        } else {

            $reqNonEntame = "
            SELECT men_lev.men_iddr, men_lev.men_id_edif, men_lev.men_num, men_rec.*
            FROM [menage_cases] men_case
            JOIN [menage_level-1] men_lev ON men_case.id = men_lev.[case-id]
            JOIN menage_menage_rec men_rec ON men_rec.[level-1-id] = men_lev.[level-1-id]
            WHERE men_case.deleted = 0 AND men_rec.men_statut = 1 AND men_rec.men_etat < 7 
            AND men_lev.men_iddr LIKE '" . $codeDep . "%'
            ";
        }

        $stmt = $this->conn->fetchAllAssociative($reqNonEntame);
        return $stmt;

        // $stmt = $this->conn->prepare($reqNonEntame);
        // $stmt->execute();

        // return $stmt->fetchAll();
    }
}

<?php

namespace App\Controller;

use App\Entity\DashboardWidget;
use App\Repository\CommunesRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\DashboardWidgetRepository;
use App\Repository\PrefecturesRepository;
use App\Repository\RegionsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends AbstractController
{

    public function __construct(ManagerRegistry $registry)
    {
        $this->conn = $registry->getManager()->getConnection();
    }



    public function nb_jour_lancement(
        $official_launch_date
    ) {
        $nb_jour = 0;
        $currentDate =  strtotime("now");
        $datediff = $currentDate - $official_launch_date;
        $nb_jour = round($datediff / (60 * 60 * 24));
        if($nb_jour > 30){
            $nb_jour = 30;
        }else if($nb_jour < 0){
            $nb_jour = 0;
        }
        return $nb_jour;
    }



    /**
     * @Route("censusmp/dashboard", name="dashboard")
     * @IsGranted("ROLE_USER")
     */
    public function dashboard(
        Request $request,
        UserRepository $userRepo
    ) {
        return $this->render('collection/dashboard.html.twig');
    }


    /**
     *dashboard dynamique
     *
     * @Route("censusmp/dynamic-dashboard", name="dynamic-dashboard")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function dynamic_dashboard(
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
    )
    {
        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $regions = $regionsRepository->findBy([], ['code' => 'ASC']);
        $prefectures = [];
        $communes = [];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {

        } 

        return $this->render('collection/dynamic_dashboard.html.twig', [
            'regions' => $regions,
            'connectedUser' => $me,
            'prefectures' => $prefectures,
            'communes' => $communes,
        ]);
    }


    /**
     *dashboard dynamique stats interviews
     *
     * @Route("censusmp/dynamic-stat-interviews", name="dynamic-stat-interviews")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function dynamic_stat_interviews(
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
    )
    {
        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $regions = $regionsRepository->findBy([], ['code' => 'ASC']);
        $prefectures = [];
        $communes = [];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {

        } 

        return $this->render('collection/dynamic_stat_inter.html.twig', [
            'regions' => $regions,
            'connectedUser' => $me,
            'prefectures' => $prefectures,
            'communes' => $communes,
        ]);
    }


    /**
     *dashboard dynamique concrétisation
     *
     * @Route("censusmp/dynamic-concretisation", name="dynamic-concretisation")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function dynamic_concretisation(
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
    )
    {
        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $regions = $regionsRepository->findBy([], ['code' => 'ASC']);
        $prefectures = [];
        $communes = [];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {

        } 

        return $this->render('collection/dynamic_concretisation.html.twig', [
            'regions' => $regions,
            'connectedUser' => $me,
            'prefectures' => $prefectures,
            'communes' => $communes,
        ]);
    }


    /**
     *dashboard dynamique indicateurs clés
     *
     * @Route("censusmp/dynamic-indic-cles", name="dynamic-indic-cles")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function dynamic_indic_cles(
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
    )
    {
        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $regions = $regionsRepository->findBy([], ['code' => 'ASC']);
        $prefectures = [];
        $communes = [];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {

        } 

        return $this->render('collection/dynamic_indic_cles.html.twig', [
            'regions' => $regions,
            'connectedUser' => $me,
            'prefectures' => $prefectures,
            'communes' => $communes,
        ]);
    }
    

    /**
     *dashboard dynamique indicateurs qualite
     *
     * @Route("censusmp/dynamic-indic-qualite", name="dynamic-indic-qualite")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
    */
    public function dynamic_indic_qualite(
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
    )
    {
        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $regions = $regionsRepository->findBy([], ['code' => 'ASC']);
        $prefectures = [];
        $communes = [];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {

        } 

        return $this->render('collection/dynamic_indic_qualite.html.twig', [
            'regions' => $regions,
            'connectedUser' => $me,
            'prefectures' => $prefectures,
            'communes' => $communes,
        ]);
    }

    
    
    /**
     *dashboard dynamique rapports
     *
     * @Route("censusmp/dynamic-rapports", name="dynamic-rapports")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
    */
    public function dynamic_rapports(
        UserRepository $userRepo,
        RegionsRepository $regionsRepository,
    )
    {
        ini_set('memory_limit', '1024M');

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        $regions = $regionsRepository->findBy([], ['code' => 'ASC']);
        $prefectures = [];
        $communes = [];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COORDINATION') || $this->isGranted('ROLE_GEOMATICIEN')
            || $this->isGranted('ROLE_COMITE_DE_VEILLE')) {

        } 

        return $this->render('collection/dynamic_rapports.html.twig', [
            'regions' => $regions,
            'connectedUser' => $me,
            'prefectures' => $prefectures,
            'communes' => $communes,
        ]);
    }

    
    /**
     * Config du dashboard
     *
     * @Route("censusmp/dashboard-config", name="config_dashboard")
     * @IsGranted("ROLE_USER")
     * 
     * @param DashboardWidgetRepository $DashboardWidgetRepository
     * @param Request $request
     * @return void
     */
    public function config_dashboard(PaginatorInterface $paginator ,
        DashboardWidgetRepository $repo, 
        Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            
            $columns = $request->get('columns') ;

            $query = $repo->buildAllDataTable($columns, $request->get('order'));

            $allUsers = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $allUsers->getTotalItemCount(),
                    'data' => $allUsers->getItems()
                ]
            );
        }

        return $this->render('collection/config_dashboard.html.twig');
    }


    /**
     * @Route("censusmp/dashboard-config/add", name="addWidgetsInDashboard")
     * @IsGranted("ROLE_USER")
     * 
     * @param KernelInterface $kernel
    */
    public function create_dashboard(
        DashboardWidgetRepository $repDash,
        Request $request,
        KernelInterface $kernel,
        EntityManagerInterface $em,
    ): Response {
        if ($request->getMethod() == "POST") {
            $errors = NULL;
            $idItem = $request->get('_id');
            // dd($idItem);
            try {
                $newWidget = new DashboardWidget();

                if($idItem != ""){
                    $newWidget = $repDash->findOneBy(['id' => $idItem]);
                }

                $newWidget
                    ->setDashboard($request->get('_page'))
                    ->setType($request->get('_type'))
                    ->setTaille($request->get('_taille'))
                    ->setCouleur($request->get('_color'))
                    ->setIcone($request->get('_icone'))
                    ->setLibelle($request->get('_libelle'))
                    ->setRequete($request->get('_requeteSql'))
                    ->setRequeteFilter1($request->get('_requeteSql_n1'))
                    ->setRequeteFilter2($request->get('_requeteSql_n2'))
                    ->setRequeteFilter3($request->get('_requeteSql_n3'))
                    ->setProfils($request->get('_roles'))
                    ->setActif(TRUE)
                    ->setOpSaisie($this->getUser())
                    ->setCreatedAt(new DateTime('now'));

                    if($request->get('_taille_mobile')){
                        $newWidget->setTailleMobile($request->get('_taille_mobile'));
                    }

                    if($request->get('_type') == 'stacked diagram'){
                        $newWidget
                            ->setDatasets($request->get('_datasets'))
                            ;
                    }

                    if($request->get('_type') == 'pyramide'){
                        $newWidget
                            ->setCouleurGauche($request->get('_color_left'))
                            ->setCouleurDroite($request->get('_color_right'))
                            ->setDatasets($request->get('_datasets'))
                            ;
                    }

                    if($request->get('_type') == 'carte'){
                        $newWidget
                            ->setCouleurHover($request->get('_color_hover'))
                            ->setCouleurMax($request->get('_color_max'))
                            ->setCouleurMin($request->get('_color_min'))
                            ->setJoinBy($request->get('_join_by'))
                            ->setValeurNull($request->get('_for_null'))
                            ->setMin($request->get('_valeur_min'))
                            ->setMax($request->get('_valeur_max'))
                            ->setUniteMesure($request->get('_unite'))
                            ->setNomCarte($request->get('_nom_carte'))
                            ;

                            $name = "";
                            $path = "";
                            if (!empty($_FILES["_geoJson"]["name"])) {
                                $name = $_FILES["_geoJson"]["name"];
                                $path = $_FILES['_geoJson']['tmp_name'];
                    
                                $extension = substr(strrchr($name, '.'), 1);
                                $dst = $kernel->getProjectDir() . "/public/cartes";
                                if (!file_exists($dst)) {
                                    mkdir($dst, 0777, true);
                                }
                                    
                                try {
                                    $fichier = $name . '.' . $extension;
                                    $dstCopie = $dst . '/' . $fichier;
                                    move_uploaded_file($path, $dstCopie);
                                    $newWidget->setCarte($fichier);
                                } catch (FileException $e) {
                                    return $this->json($e->getMessage(), 500);
                                }
                            }
                    
                    }

                    if($request->get('_type') == 'tableau'){
                        $newWidget
                            ->setColonnes($request->get('_col'))
                            ;
                    }

                    if($request->get('_type') == 'horizontal diagram' || $request->get('_type') == 'vertical diagram' || $request->get('_type') == 'linear' || $request->get('_type') == 'pie'){
                        if (!str_contains($request->get('_requeteSql'), 'nom')) { 
                            $errors = "Le résultat de la requête doit comporter la colonne 'nom' ";
                        }else if(!str_contains($request->get('_requeteSql'), 'valeur')){
                            $errors = "Le résultat de la requête doit comporter la colonne 'valeur' ";
                        }
                    }else if($request->get('_type') == 'stacked diagram' || $request->get('_type') == 'pyramide'){
                        if(!str_contains($request->get('_datasets'), ',')){
                            $errors = "Les éléments de la liste des 'Datasets' doivent être séparés par des virgules";
                        }else{
                            $composants = array_map('trim', explode(',', $request->get('_datasets'))) ;
                            foreach($composants as $oneDatasets){
                                if(!str_contains($request->get('_requeteSql'), $oneDatasets)){
                                    $errors = "Le résultat de la requête doit comporter la colonne '".$oneDatasets."' ";
                                }
                            }
                        }
                    }else if($request->get('_type') == 'carte'){
                        if (!str_contains($request->get('_requeteSql'), 'region')) { 
                            $errors = "Le résultat de la requête doit comporter la colonne 'region' ";
                        }else if(!str_contains($request->get('_requeteSql'), 'valeur')){
                            $errors = "Le résultat de la requête doit comporter la colonne 'valeur' ";
                        }
                    }else if($request->get('_type') == 'tableau'){
                        if(!str_contains($request->get('_col'), ',')){
                            $errors = "Les éléments de la liste des colonnes doivent être séparés par des virgules";
                        }else{
                            $cols = array_map('trim', explode(',', $request->get('_col'))) ;
                            foreach($cols as $oneCol){
                                if(!str_contains($request->get('_requeteSql'), $oneCol)){
                                    $errors = "Le résultat de la requête doit comporter la colonne '".$oneCol."' ";
                                }
                            }
                        }
                    }
                // var_dump($newWidget); die;

                if($errors == NULL){
                    $em->persist($newWidget);
                    $em->flush();
                }else{
                    return $this->json($errors, 500);
                }
            } catch (\Exception $th) {
                $message = $th->getMessage();
                return $this->json($message, 500);
            }
    
            return $this->json("Indicateur ajouté avec succès", 200);

        }
    }


    /**
     * Permet d'afficher les indicateurs sur le tableau de bord
     * 
     * @Route("censusmp/dashboard-config/display/{page}/{niveau}/{code}", name="generateIndicators", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     * @param Request $request
     * @return JsonResponse
    **/
    public function displayIndicators($page, $niveau, $code, DashboardWidgetRepository $repo): JsonResponse
    {
        $data = array();

        $rolesUser = $this->getUser()->getRoles();
        // dd($rolesUser);
        try {
            $indics = $repo->findBy(['dashboard' => $page, 'actif' => TRUE], ['id' => 'ASC']);
            foreach ($indics as $ind) {

                $arr = array_intersect($rolesUser, $ind->getProfils());
                $key = array_search("ROLE_USER", $arr);
                if (false !== $key) {
                    unset($arr[$key]);
                }
                // dd(count($arr));
                if(count($arr) > 0){
                    $maRequete = $ind->getRequete();
                    if($niveau == '1' && $ind->getRequeteFilter1() != NULL && $ind->getRequeteFilter1() != ""){
                        $maRequete = str_replace('filtre', $code, $ind->getRequeteFilter1());
                    }
                    else if($niveau == '2' && $ind->getRequeteFilter2() != NULL && $ind->getRequeteFilter2() != ""){
                        $maRequete = str_replace('filtre', $code, $ind->getRequeteFilter2());
                    }
                    else if($niveau == '3' && $ind->getRequeteFilter3() != NULL && $ind->getRequeteFilter3() != ""){
                        $maRequete = str_replace('filtre', $code, $ind->getRequeteFilter3());
                    }
    
                    if($ind->getType() == 'blocks' || $ind->getType() == 'socials'){
                        $resultat = $this->forWidget($maRequete);
                        //var_dump($resultat); die;
                        array_push($data, [
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'couleur' => $ind->getCouleur(),
                            'libelle' => $ind->getLibelle(),
                            'icone' => $ind->getIcone(),
                            'valeur' => $resultat
                        ]);
                    } 
                    else if($ind->getType() == 'tables'){
                        $resultat = $this->forWidget($maRequete);
                        //var_dump($resultat); die;
                        array_push($data, [
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'couleur' => $ind->getCouleur(),
                            'darker_couleur' => $this->colorBrightness($ind->getCouleur(), -0.3),
                            'libelle' => $ind->getLibelle(),
                            'icone' => $ind->getIcone(),
                            'valeur' => $resultat
                        ]);
                    } 
                    else if($ind->getType() == 'carte'){
                        $resultat = $this->forDiagram($maRequete);
                        $tab_res = array();
                        foreach($resultat as $result){
                            array_push($tab_res, array($result['region'], round($result['valeur'], 1) ));
                        }
                        // var_dump($tab_res); die;
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'couleur_min' => $ind->getCouleurMin(),
                            'couleur_max' => $ind->getCouleurMax(),
                            'couleur_hover' => $ind->getCouleurHover(),
                            'libelle' => $ind->getLibelle(),
                            'valeur_min' => $ind->getMin(),
                            'valeur_max' => $ind->getMax(),
                            'unite_mesure' => $ind->getUniteMesure(),
                            'property' => $ind->getJoinBy(),
                            'valeur_null' => $ind->getValeurNull(),
                            'carte' => $ind->getCarte(),
                            'nom_carte' => $ind->getNomCarte(),
                            'donnees' => $tab_res
                        ]);
                    }
                    else if($ind->getType() == 'horizontal diagram'){
                        $resultat = $this->forDiagram($maRequete);
                        // dd($resultat);
                        $Labels = array();
                        $Datas = array();
                        foreach ($resultat as $row) {
                            array_push($Labels, $row['nom']);
                            array_push($Datas, $row['valeur']);
                        }
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'libelle' => $ind->getLibelle(),
                            'couleur' => $ind->getCouleur(),
                            'labels' => $Labels,
                            'datas' => $Datas
                        ]);
                    } 
                    else if($ind->getType() == 'vertical diagram'){
                        $resultat = $this->forDiagram($maRequete);
                        // dd($resultat);
                        $Datas = array();
                        foreach ($resultat as $row) {
                            array_push($Datas, [
                                'name' => $row['nom'],
                                'y' => $row['valeur'],
                                'drilldown' => $row['nom'],
                            ]);
                        }
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'libelle' => $ind->getLibelle(),
                            'couleur' => $ind->getCouleur(),
                            'datas' => $Datas
                        ]);
                    } 
                    else if($ind->getType() == 'stacked diagram'){
                        $resultat = $this->forDiagram($maRequete);
                        $composants = array_map('trim', explode(',', $ind->getDatasets())) ;
                        // dd($resultat);
                        $Labels = array();
                        $Datasets = array();
                        $Scales = array();
                        $iterate = 1;
                        $display = true;
                        $lighter = 0.4;
                        $barThickness = 10;
    
                        foreach ($resultat as $row) {
                            array_push($Labels, $row['nom']);
                        }
                        // dd($composants);
                        foreach($composants as $comp){
                            $Datas = array();
                            $colorSet = $ind->getCouleur(); 
                            
                            if($iterate > 1){
                                $colorSet = $this->colorBrightness($ind->getCouleur(), $lighter);
                                $display = false;
                            }
    
                            $leContent = [
                                'display' => $display,
                                'stacked' => true,
                                'id' => "bar-y-axis".$iterate,
                                'barThickness' => $barThickness,
                                'maxBarThickness' => ($barThickness*2),
                                'minBarLength' => ($barThickness*4)/5,
                            ];
    
                            if($iterate > 1){
                                $leContent = [
                                    'display' => $display,
                                    'stacked' => true,
                                    'id' => "bar-y-axis".$iterate,
                                    'barThickness' => $barThickness,
                                    'maxBarThickness' => ($barThickness*2),
                                    'minBarLength' => ($barThickness*4)/5,
                                    'type' => 'category',
                                    'categoryPercentage' => 0.8,
                                    'barPercentage' => 0.9,
                                    'gridLines' => [
                                        'offsetGridLines' => true
                                    ],
                                    'offset' => true
                                ];
                            }
    
                            // dd($resultat);
                            foreach ($resultat as $row) {
                                array_push($Datas, $row[$comp]);
                                // dd($comp);
                            }
                            array_push($Datasets, [
                                'label' => $comp,
                                'backgroundColor' => $colorSet,
                                'borderWidth' => 1,
                                'data' => $Datas,
                                'yAxisID' => "bar-y-axis".$iterate,
                            ]);
                            array_push($Scales, $leContent);
    
                            $iterate++;
                            $lighter = $lighter +0.3;
                            $barThickness = $barThickness + 10;
                        }
                        // dd($Labels, $Datasets, $Scales);
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'libelle' => $ind->getLibelle(),
                            'couleur' => $ind->getCouleur(),
                            'labels' => $Labels,
                            'datasets' => $Datasets,
                            'scales' => $Scales,
                        ]);
                    } 
                    else if($ind->getType() == 'tableau'){
                        $resultat = $this->forDiagram($maRequete);
                        $tabHeader = array_map('trim', explode(',', $ind->getColonnes())) ;
                        // dd($resultat , $tabHeader);
    
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'couleur_header' => $ind->getCouleur(),
                            'libelle' => $ind->getLibelle(),
                            'colonnes' => $tabHeader,
                            'datas' => $resultat
                        ]);
                    } 
                    else if($ind->getType() == 'linear'){
                        $resultat = $this->forDiagram($maRequete);
                        $Datas = array();
                        foreach ($resultat as $row) {
                            array_push($Datas, [
                                'date' => $row['nom'],
                                'value' => $row['valeur'],
                            ]);
                        }
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'libelle' => $ind->getLibelle(),
                            'couleur' => $ind->getCouleur(),
                            'datas' => $Datas
                        ]);
                    } 
                    else if($ind->getType() == 'pyramide'){
                        $resultat = $this->forDiagram($maRequete);
                        $composants = array_map('trim', explode(',', $ind->getDatasets())) ;
                        // dd($resultat);
                        $Labels = array();
                        $Datasets = array();
                        $iterate = 1;
    
                        foreach ($resultat as $row) {
                            array_push($Labels, $row['nom']);
                        }
                        // dd($composants);
                        foreach($composants as $comp){
                            $Datas = array();
                            $colorSet = $ind->getCouleurGauche(); 
                            
                            if($iterate > 1){
                                $colorSet = $ind->getCouleurDroite();
                            }
    
                            // dd($resultat);
                            foreach ($resultat as $row) {
                                array_push($Datas, $row[$comp]);
                                // dd($comp);
                            }
    
                            if($iterate == 1){
                                $Datas = array_map(function($el) { return 0-$el; }, $Datas);
                            }
    
                            array_push($Datasets, [
                                'label'=> $comp,
                                'stack'=> "Stack 0",
                                'backgroundColor'=> $colorSet,
                                'data' => $Datas,
                            ]);
    
                            $iterate++;
                        }
                        // dd($Labels, $Datasets);
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'libelle' => $ind->getLibelle(),
                            'couleur' => $ind->getCouleur(),
                            'labels' => $Labels,
                            'datasets' => $Datasets,
                        ]);
                    } 
                    else if($ind->getType() == 'pie'){
                        $resultat = $this->forDiagram($maRequete);
                        // dd($resultat);
                        $Labels = array();
                        $Datas = array();
                        $couleurs = array();
                        $couleurs_hover = array();
    
                        foreach ($resultat as $row) {
                            array_push($Labels, $row['nom']);
                            array_push($Datas, $row['valeur']);
                            
                            $colorS = $this->randomColor();
                            array_push($couleurs, $colorS);
                            array_push($couleurs_hover, $this->colorBrightness($colorS, 0.4));
                        }
                        array_push($data, [
                            'id' => $ind->getId(),
                            'type' => $ind->getType(),
                            'taille' => $ind->getTaille(),
                            'taille_mobile' => $ind->getTailleMobile(),
                            'libelle' => $ind->getLibelle(),
                            'couleurs' => $couleurs,
                            'couleurs_hover' => $couleurs_hover,
                            'labels' => $Labels,
                            'datas' => $Datas
                        ]);
                    } 
                }


            }

            return $this->json($data);
        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), 500);
        }
    }


    /**
     * Supprimer des indicateurs
     * 
     * @Route("censusmp/dashboard/delete/{id}", name="indicator_delete", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     */
    public function deleteIndic($id,
        DashboardWidgetRepository $dashRepo,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL
    ): Response {

        try {
            $indicateur = $dashRepo->findOneBy(['id' => $id]);
            $defaultEntityManager->remove($indicateur);

            $defaultEntityManager->flush();
        } catch (\Exception $th) {
            $message = $th->getMessage();
            return $this->json($message, 500);
        }
        return $this->json("indicateur supprimé", 200);
    }


    /**
     * changer statut des indicateurs
     * 
     * @Route("censusmp/dashboard/change/{id}", name="indicator_status_change", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     */
    public function statChangeIndic($id,
        DashboardWidgetRepository $dashRepo,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL
    ): Response {

        $status = FALSE;

        try {
            $indicateur = $dashRepo->findOneBy(['id' => $id]);
            if($indicateur){
                $status = !$indicateur->isActif();
            }

            $indicateur->setActif($status);
            $defaultEntityManager->persist($indicateur);
            $defaultEntityManager->flush();
        } catch (\Exception $th) {
            $message = $th->getMessage();
            return $this->json($message, 500);
        }

        $msg_retour = ($status == TRUE) ? "activé" : "desactivé";
        return $this->json("Indicateur ".$msg_retour, 200);
    }


    /**
     * changer statut des indicateurs
     * 
     * @Route("censusmp/dashboard/update/{id}", name="get_indicator_items", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     */
    public function updateIndic($id,
        DashboardWidgetRepository $dashRepo
    ): Response {

        try {
            $indicateur = $dashRepo->findOneBy(['id' => $id]);
        } catch (\Exception $th) {
            $message = $th->getMessage();
            return $this->json($message, 500);
        }
        return  new JsonResponse($indicateur);
    }


    // USES FUNCTIONS
    public function forWidget($qb): float|null
    {
        try {
            $stmt = $this->conn->prepare($qb);
            $resulats = $stmt->executeQuery()->fetchOne();

            return $resulats;
        } catch (\Throwable $th) {
            //throw $th;
        }

        return null;
    }

    public function forDiagram($qb): array
    {
        try {
            $stmt = $this->conn->prepare($qb);
            $resulats = $stmt->executeQuery()->fetchAll();
            return $resulats;
        } catch (\Throwable $th) {
            //throw $th;
        }

        return [];
    }

    public function colorBrightness(String $hex, float $percent) {
        $hex = ltrim($hex, '#');

            if (strlen($hex) == 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            $hex = array_map('hexdec', str_split($hex, 2));

            foreach ($hex as & $color) {
                $adjustableLimit = $percent < 0 ? $color : 255 - $color;
                $adjustAmount = ceil($adjustableLimit * $percent);
                // dd($percent);

                $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
            }
            return '#' . implode($hex);
    }


    public function randomColor(){
        $rand = str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);
        return '#' . $rand;
    }
}

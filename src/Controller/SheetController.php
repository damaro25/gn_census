<?php

namespace App\Controller;

use App\Entity\Pv;
use App\Utils\Utils;
use App\Entity\CentroideZs;
use App\Entity\Departements;
use App\Entity\SheetCentroides;
use App\Repository\PvRepository;
use App\Entity\CentroideParcellairesDr;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Controller\ConcessionsController;
use App\Repository\CentroideDrRepository;
use App\Repository\CentroideZcRepository;
use App\Repository\CentroideZsRepository;
use App\Repository\CentroideQvhRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CentroideDrZsZcRepository;
use App\Repository\SheetCentroidesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CentroideParcellairesDrRepository;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class SheetController extends AbstractController
{
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }
    /**
     * @Route("/sheets/centroideslist", name="app_sheets_centrodies")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, SheetCentroidesRepository $repo, PaginatorInterface $paginator): Response
    {
        //ini_set('memory_limit', '4096M');
        //set_time_limit(0);

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), "centroide");

            $zs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $zs->getTotalItemCount(),
                    'data' => $zs->getItems()
                ]
            );
        }

        return $this->render('sheets/sheet_centroides.html.twig');
    }

    /**
     * Upload les centroides
     * 
     * @Route("/sheets/centrodies-upload", name="app_sheets_centrodies_upload")
     * @IsGranted("ROLE_USER")
     * 
     * @param Request $request
     * @throws \Exception
     */
    public function upload(
        Request $request,
        KernelInterface $kernel,
        SheetCentroidesRepository $sheetCentroidesRepository,
        CentroideParcellairesDrRepository $repo,
        CentroideZsRepository $zsRepo,
        CentroideZcRepository $zcRepo,
        CentroideDrRepository $drRepo,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL
    ) {
        //ini_set('memory_limit', '4096M');
        //set_time_limit(0);

        $filedest = $this->getParameter('sheetCentroides');
        $isCpy = false;

        $name = "";
        $path = "";

        try {
            //code...
            if (!empty($_FILES["file"]["name"])) {
                $name = $_FILES["file"]["name"];
                $path = $_FILES['file']['tmp_name'];
    
                if (!file_exists($filedest)) {
                    mkdir($filedest, 0777, true);
                }
    
                try {
                    move_uploaded_file($path, $filedest . "/" . $name);
                } catch (FileException $e) {
                    // dd($e);
                }
                $spreadsheet = IOFactory::load($filedest . "/" . $name); // Here we are able to read from the excel file 
    
                // récupère le nom des colonnes
                $mapColumns = ZsController::mapExcelColumns($spreadsheet);
    
                // TODO: verifie l'existence des col d'entêtes 
                $excelColonnes = [
                    'ID_EDIFICE',
                    'AJOUT',
                    'DESCRIPTIF',
                    'OBS', 'NUM_CONCESSION',
                    'REG', 'COD_REG',
                    'DEPT', 'COD_DEPT',
                    'CAV', 'COD_CAV',
                    'CCRCA', 'COD_CCRCA',
                    'QVH', 'COD_QVH',
                    'COD_DR_2012',
                    'LONGITUDE', 'LATITUDE',
                    'DESIGNATION',
                    'TYPE_RATTACHEMENT',
                    'ETAT_NIVEAU',
                    'ADRESSE',
                    'NUM_TELEPHONE',
                    'NBR_BATIMENT',
                    'NBR_MENAGE',
                    'POPULATION_CONCESSION',
                    'NUM_DEPT',
                    'NUM_CAV',
                    'NUM_CCRCA',
                    'TYPE_QVH',
                    'MILIEU',
                    'NUM_TYP_RATTACH',
                    'COD_TYP_RATTACH',
                    'ZSUP',
                    'NUM_ZSUP',
                    'ZCONT',
                    'NUM_ZCONT',
                    'COD_ZCONT',
                    'COD_ZSUP',
                    'QVH_2022',
                    'NUM_QVH_2022',
                    'COD_QVH_2022',
                    'DR_2022',
                    'COD_DR_2022',
                    'NUM_CONC_2022',
                    'COD_CONC',
                    'COD_CONCDR',
                    'COD_CONCLO',
                    'LOGIN_AGENT', // login agent
                    'STATUT_CONCESSION',
                    'TYPE_HABITAT',
                    'AUTRE_HABITAT',
                    'TYPE_INFRASTRUCTURE',
                    'NBR_HABITATION',
                    'CASE_ID',
                ];
    
                $col = ZsController::excelNotFoundColumnException($excelColonnes, $mapColumns);
    
                if (!empty($col)) {
                    Utils::deleteExcelTmpFile($name, $filedest . "/");
                    return $this->json("Le fichier n'a pas la colonne [" . $col . "]");
                }
    
                $objectIdIndex = array_search("ID_EDIFICE", $mapColumns, true);
                $ajoutIndex = array_search("AJOUT", $mapColumns, true);
                $descriptifIndex = array_search("DESCRIPTIF", $mapColumns, true);
                $obsIndex = array_search("OBS", $mapColumns, true);
                $numConcessionIndex = array_search("NUM_CONCESSION", $mapColumns, true);
                $regIndex = array_search("REG", $mapColumns, true);
                $codRegIndex = array_search("COD_REG", $mapColumns, true);
                $deptIndex = array_search("DEPT", $mapColumns, true);
                $codDeptIndex = array_search("COD_DEPT", $mapColumns, true);
                $cavIndex = array_search("CAV", $mapColumns, true);
                $codCavIndex = array_search("COD_CAV", $mapColumns, true);
                $ccrcaIndex = array_search("CCRCA", $mapColumns, true);
                $codCcrcaIndex = array_search("COD_CCRCA", $mapColumns, true);
    
                $qvhIndex = array_search("QVH", $mapColumns, true);
                $codQvhIndex = array_search("COD_QVH", $mapColumns, true);
                $codDr2012Index = array_search("COD_DR_2012", $mapColumns, true);
                $longititudeIndex = array_search("LONGITUDE", $mapColumns, true);
                $latitudeIndex = array_search("LATITUDE", $mapColumns, true);
                $designationIndex = array_search("DESIGNATION", $mapColumns, true);
                $typeRattachementIndex = array_search("TYPE_RATTACHEMENT", $mapColumns, true);
                $etatNiveauIndex = array_search("ETAT_NIVEAU", $mapColumns, true);
                $adresseIndex = array_search("ADRESSE", $mapColumns, true);
                $numTelephoneIndex = array_search("NUM_TELEPHONE", $mapColumns, true);
                $nbrBatimentIndex = array_search("NBR_BATIMENT", $mapColumns, true);
                $nbrMenageIndex = array_search("NBR_MENAGE", $mapColumns, true);
                $populationConcessionIndex = array_search("POPULATION_CONCESSION", $mapColumns, true);
    
                $numDeptIndex = array_search("NUM_DEPT", $mapColumns, true);
                $numCavIndex = array_search("NUM_CAV", $mapColumns, true);
                $numCcrcaIndex = array_search("NUM_CCRCA", $mapColumns, true);
                $numQvhIndex = array_search("NUM_QVH", $mapColumns, true);
                $typeQvhIndex = array_search("TYPE_QVH", $mapColumns, true);
                $milieuIndex = array_search("MILIEU", $mapColumns, true);
    
                // $typRattachIndex = array_search("TYP_RATTACH", $mapColumns, true);
                $numTypRattachIndex = array_search("NUM_TYP_RATTACH", $mapColumns, true);
                $codTypRattachIndex = array_search("COD_TYP_RATTACH", $mapColumns, true);
                $vilAppartIndex = "";
    
                $zSupIndex = array_search("ZSUP", $mapColumns, true);
                $numZsupIndex = array_search("NUM_ZSUP", $mapColumns, true);
                $zcontIndex = array_search("ZCONT", $mapColumns, true);
                $numlZcontIndex = array_search("NUM_ZCONT", $mapColumns, true);
                $codZcontIndex = array_search("COD_ZCONT", $mapColumns, true);
                $codZsupIndex = array_search("COD_ZSUP", $mapColumns, true);
    
                // nouvelle colonne ajoutées 08/06/2022
                $qvh2022Index = array_search("QVH_2022", $mapColumns, true);
                $numQvh2022Index = array_search("NUM_QVH_2022", $mapColumns, true);
                $codQvh2022Index = array_search("COD_QVH_2022", $mapColumns, true);
                $dr2022Index = array_search("DR_2022", $mapColumns, true);
                $codDr2022Index = array_search("COD_DR_2022", $mapColumns, true);
                $numConc2022Index = array_search("NUM_CONC_2022", $mapColumns, true);
                $codConcIndex = array_search("COD_CONC", $mapColumns, true);
                $codConcDrIndex = array_search("COD_CONCDR", $mapColumns, true);
                $codConCloIndex = array_search("COD_CONCLO", $mapColumns, true);
    
                $loginAgentIndex = array_search("LOGIN_AGENT", $mapColumns, true);
                $statutConcessionIndex = array_search("STATUT_CONCESSION", $mapColumns, true);
                $typeHabitatIndex = array_search("TYPE_HABITAT", $mapColumns, true);
                $autreHabitatIndex = array_search("AUTRE_HABITAT", $mapColumns, true);
                $typeInfrastructureIndex = array_search("TYPE_INFRASTRUCTURE", $mapColumns, true);
                $nbrHabitatIndex = array_search("NBR_HABITATION", $mapColumns, true);
                $caseIdIndex = array_search("CASE_ID", $mapColumns, true);
    
    
                $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line 
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array
    
                // incrémente le compteur des éléments non enregistrés 
                $countSaving = 0;
                $countUpdating = 0;
    
                // 
                $idEdificeError = "";
                $codqvhError = "";
    
    
                try {
                    $defaultEntityManager->beginTransaction();
    
                    $label = "copié";
                    $sheetCentroide = $sheetCentroidesRepository->findOneBy(["fileName" => $name]);
                    if ($sheetCentroide) {
                        $sheetCentroide
                            // ->setFileName($name)
                            ->setUpdateAt(new \DateTime())
                            ->setOpsaisi($this->getUser());
                        $defaultEntityManager->persist($sheetCentroide);
                        $label = "remplacé";
                    } else {
                        $sheetCentroide = new SheetCentroides();
                        $sheetCentroide
                            ->setFileName($name)
                            ->setCreateAt(new \DateTime())
                            ->setOpsaisi($this->getUser())
                            ->setFileType("centroide");
                        $defaultEntityManager->persist($sheetCentroide);
                    }

                    $filesystem = new Filesystem();
    
                    foreach ($sheetData as $Row) {
                        $objectId = $Row[$objectIdIndex];
                        $ajout = $Row[$ajoutIndex];
                        $descriptif = $Row[$descriptifIndex];
                        $obs = $Row[$obsIndex];
                        $numConcession = $Row[$numConcessionIndex];
                        $reg = $Row[$regIndex];
                        $codReg = $Row[$codRegIndex];
                        $dept = $Row[$deptIndex];
                        $codDept = $Row[$codDeptIndex];
                        $cav = $Row[$cavIndex];
                        $codCav = $Row[$codCavIndex];
                        $ccrca = $Row[$ccrcaIndex];
                        $codCcrca = $Row[$codCcrcaIndex];
    
                        $qvh = $Row[$qvhIndex];
                        $codQvh = $Row[$codQvhIndex];
                        $codDr2012 = $Row[$codDr2012Index];
                        $longititude = $Row[$longititudeIndex];
                        $latitude = $Row[$latitudeIndex];
                        $designation = $Row[$designationIndex];
                        $typeRattachement = $Row[$typeRattachementIndex];
                        $etatNiveau = $Row[$etatNiveauIndex];
                        $adresse = $Row[$adresseIndex];
                        $numTelephone = $Row[$numTelephoneIndex];
                        $nbrBatiment = $Row[$nbrBatimentIndex];
                        $nbrMenage = $Row[$nbrMenageIndex];
                        $populationConcession = $Row[$populationConcessionIndex];
    
                        $codQvhPropositionDecoupage = "";
                        $codDrPropositionDecoupage = "";
                        $drProp = "";
                        $statutConcession = $Row[$statutConcessionIndex];
    
                        $dr2012 = "";
                        $statConcParc = "";
                        $numConcProp = "";
                        $niveau = "";
                        $numDept = $Row[$numDeptIndex];
                        $numCav = $Row[$numCavIndex];
                        $numCcrca = $Row[$numCcrcaIndex];
                        $numQvh = "";
                        $typeQvh = $Row[$typeQvhIndex];
                        $milieu = $Row[$milieuIndex];
                        $Arr = "";
                        $numTypRattach = $Row[$numTypRattachIndex];
                        $codTypRattach = $Row[$codTypRattachIndex];
                        $vilAppart = "";
                        $zSup = $Row[$zSupIndex];
                        $numZsup = $Row[$numZsupIndex];
                        $zcont = $Row[$zcontIndex];
                        $numlZcont = $Row[$numlZcontIndex];
                        $codZcont = $Row[$codZcontIndex];
                        $codZsup = $Row[$codZsupIndex];
    
                        // Nouvelles colonnes ajoutées 08/06/2022
                        $qvh2022 = $Row[$qvh2022Index];
                        $numQvh2022 = $Row[$numQvh2022Index];
                        $codQvh2022 = $Row[$codQvh2022Index];
                        $dr2022 = $Row[$dr2022Index];
                        $codDr2022 = $Row[$codDr2022Index];
                        $numConc2022 = $Row[$numConc2022Index];
                        $codConc = $Row[$codConcIndex];
                        $codConcDr = $Row[$codConcDrIndex];
                        $codConClo = $Row[$codConCloIndex];
                        // $descBatit = $Row[$descBatitIndex];
                        // $typMen = $Row[$typMenIndex];
                        $loginAgent = $Row[$loginAgentIndex];
                        $typeHabitat = $Row[$typeHabitatIndex];
                        $autreHabitat = $Row[$autreHabitatIndex];
                        $typeInfrastructure = $Row[$typeInfrastructureIndex];
                        $nbrHabitation = $Row[$nbrHabitatIndex];
                        $caseId = $Row[$caseIdIndex];
    
                        if (!empty($codQvh2022)) {
                            // test si edifice et codqvh sont null
                            if (empty($objectId) && !empty($codQvh2022)) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                return $this->json("LA COLONNE [ID EDIFICE] EST OBLIGATOIRE A LA LIGNE AVEC LES COORDONNEES: ".$longititude." / ".$latitude);
                            }
                            // TODO: Test l'existence des ZS et ZC
                            $zs = $zsRepo->findOneBy(["codZsup" => $codZsup]);
                            $zc = $zcRepo->findOneBy(["codZcont" => $codZcont, "codZsup" => $codZsup]);
                            $dr = $drRepo->findOneBy(["codDr2022" => $codDr2022]);
                            /*if ($zs == null) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                return $this->json("La Zone Supervision [$codZsup] n'existe pas, veuillez la créée !", 500);
                            } else if ($zc == null) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                return $this->json("La Zone Contrôle [zc = $codZcont | zs = $codZsup] n'existe pas", 500);
                            } else */
                            if ($dr == null) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                return $this->json("Le DR [cod_DR_2022 = $codDr2022] n'existe pas");
                            }
    
                            // Vérifie le nombre de position du codDR2022
                            if (strlen($codDr2022) != 12) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                $currentPosition = strlen($codDr2022);
                                return $this->json("Attention le COD_DR_2022 doit être sur 12 positions [$codDr2022 sur $currentPosition position(s)] !!!");
                            }
                            // Vérifie le nombre de position du codQVH
                            if (strlen($codQvh2022) != 13) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                $currentPosition = strlen($codQvh);
                                return $this->json("Attention le COD_QVH doit être sur 13 positions [$codQvh sur $currentPosition position(s)] !!!");
                            }
                            // Vérifie si case_id n'est pas nul
                            if (empty($caseId)) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                                return $this->json("LA COLONNE [CASE_ID] EST OBLIGATOIRE A LA LIGNE AVEC LES COORDONNEES: ".$longititude." / ".$latitude);
                            }
                            $lat = str_replace(',', '.', $latitude);
                            $lon = str_replace(',', '.', $longititude);
                            // $gps = $repo->findOneBy(['latitude' => $lat, 'longitude' => $lon]);
                            // if ($gps != null) {
                            //     Utils::deleteExcelTmpFile($name, $filedest . "/");
                            //     return $this->json("Tentative d'ajout de doublons de coordonnées GPS [LAT=" . $lat . " et LON=" . $lon . "]", 500);
                            // }
                            $isConcession = NULL;
                            if ($request->get("mode") == "update") {
                                $isConcession = $repo->findOneBy(['longitude' => $lon, 'latitude' => $lat]);
                            }
    
                            if ($isConcession == NULL) {
                                // récupère le prochain id edifice pour le COD_DR_2022 en question
                                $numEdifice = ConcessionsController::nextIdEdificeDR($repo, $codDr2022);
                                // $numEdifice = "undefined";
    
                                $newConcession = new CentroideParcellairesDr();
                                $newConcession
                                    ->setObjectid($objectId) // ID EDIFICE CARTO
                                    ->setIdEdificeDenombrement($numEdifice) // ID EDIFICE Dénombrement
                                    ->setAjout($ajout)
                                    ->setDescriptif($descriptif)
                                    ->setObs($obs)
                                    ->setNumConcession($numConcession)
                                    ->setReg($reg)
                                    ->setCodReg($codReg)
                                    ->setDept($dept)
                                    ->setCodDept($codDept)
                                    ->setCav($cav)
                                    ->setCodCav($codCav)
                                    ->setCcrca($ccrca)
                                    ->setCodCcrca($codCcrca)
                                    ->setQvh($qvh)
                                    ->setCodQvh($codQvh)
                                    ->setCodDr2012($codDr2012)
                                    ->setLongitude($lon)
                                    ->setLatitude($lat)
                                    ->setDesignation($designation)
                                    ->setTypeRattachement($typeRattachement)
                                    ->setEtatNiveau($etatNiveau)
                                    ->setAdresse($adresse)
                                    ->setNumTelephone($numTelephone)
                                    ->setNbrBatiment($nbrBatiment)
                                    ->setNbrMenage($nbrMenage)
                                    ->setPopulationConcession($populationConcession)
                                    ->setCodeQvhPropositionDecoupage($codQvhPropositionDecoupage)
                                    ->setCodeDrPropositionDecoupage($codDrPropositionDecoupage)
                                    ->setDrProp($drProp)
                                    ->setStatutConcession($statutConcession)
                                    ->setDr2012($dr2012)
                                    ->setStatConcParc($statConcParc)
                                    ->setNumConcProp($numConcProp)
                                    ->setNiveau($niveau)
                                    ->setNumDept($numDept)
                                    ->setNumCav($numCav)
                                    ->setNumCcrca($numCcrca)
                                    ->setNumQvh($numQvh)
                                    ->setTypeQvh($typeQvh)
                                    ->setMilieu($milieu)
                                    ->setArr($Arr)
                                    ->setTypRattach($typeRattachement)
                                    ->setNumTypRattach($numTypRattach)
                                    ->setCodTypRattach($codTypRattach)
                                    ->setVilAppart($vilAppart)
                                    ->setZsup($zSup)
                                    ->setNumZsup($numZsup)
                                    ->setZcont($zcont)
                                    ->setNumZcont($numlZcont)
                                    ->setCodZcont($codZcont)
                                    ->setCodZsup($codZsup)
                                    // Nouvelles col. ajoutées 08/06/2022
                                    ->setQvh2022($qvh2022)
                                    ->setCodQvh2022($codQvh2022)
                                    ->setNumQvh2022($numQvh2022)
                                    ->setDr2022($dr2022)
                                    ->setCodDr2022($codDr2022)
                                    ->setNumConc2022($numConc2022)
                                    ->setCodConc($codConc)
                                    ->setCodConcDr($codConcDr)
                                    ->setCodConClo($codConClo)
                                    // ->setDescBatit($descBatit)
                                    // ->setTypMen($typMen)
    
                                    ->setCreateAt(new \Datetime())
                                    ->setOpSaisi($this->getUser())
    
                                    // 12/06/2022
                                    ->setZs($zs)
                                    ->setZc($zc)
                                    ->setDr($dr)
    
                                    ->setLoginAgent($loginAgent)
                                    ->setTypeHabitat($typeHabitat)
                                    ->setAutreHabitat($autreHabitat)
                                    ->setTypeInfrastructure($typeInfrastructure)
                                    ->setNombreHabitation($nbrHabitation)
                                    ->setCaseId($caseId)
    
                                    ->setFichier($sheetCentroide);
    
                                $defaultEntityManager->persist($newConcession);
    
                                $countSaving++;
                            } else {
                                $isConcession
                                    // ->setObjectid($objectId)
                                    ->setCodQvh($codQvh)
                                    ->setLongitude($lon)
                                    ->setLatitude($lat)
    
                                    ->setAjout($ajout)
                                    ->setDescriptif($descriptif)
                                    ->setObs($obs)
                                    ->setNumConcession($numConcession)
                                    ->setReg($reg)
                                    ->setCodReg($codReg)
                                    ->setDept($dept)
                                    ->setCodDept($codDept)
                                    ->setCav($cav)
                                    ->setCodCav($codCav)
                                    ->setCcrca($ccrca)
                                    ->setCodCcrca($codCcrca)
                                    ->setQvh($qvh)
                                    ->setCodDr2012($codDr2012)
                                    ->setDesignation($designation)
                                    ->setTypeRattachement($typeRattachement)
                                    ->setEtatNiveau($etatNiveau)
                                    ->setAdresse($adresse)
                                    ->setNumTelephone($numTelephone)
                                    ->setNbrBatiment($nbrBatiment)
                                    ->setNbrMenage($nbrMenage)
                                    ->setPopulationConcession($populationConcession)
                                    ->setCodeQvhPropositionDecoupage($codQvhPropositionDecoupage)
                                    ->setCodeDrPropositionDecoupage($codDrPropositionDecoupage)
                                    ->setDrProp($drProp)
                                    ->setStatutConcession($statutConcession)
                                    ->setDr2012($dr2012)
                                    ->setStatConcParc($statConcParc)
                                    ->setNumConcProp($numConcProp)
                                    ->setNiveau($niveau)
                                    ->setNumDept($numDept)
                                    ->setNumCav($numCav)
                                    ->setNumCcrca($numCcrca)
                                    ->setNumQvh($numQvh)
                                    ->setTypeQvh($typeQvh)
                                    ->setMilieu($milieu)
                                    ->setArr($Arr)
                                    ->setTypRattach($typeRattachement)
                                    ->setNumTypRattach($numTypRattach)
                                    ->setCodTypRattach($codTypRattach)
                                    ->setVilAppart($vilAppart)
                                    ->setZsup($zSup)
                                    ->setNumZsup($numZsup)
                                    ->setZcont($zcont)
                                    ->setNumZcont($numlZcont)
                                    ->setCodZcont($codZcont)
                                    ->setCodZsup($codZsup)
                                    // Nouvelles col. ajoutées 08/06/2022
                                    ->setQvh2022($qvh2022)
                                    // ->setCodQvh2022($codQvh2022)
                                    // ->setCodDr2022($codDr2022)
                                    ->setNumQvh2022($numQvh2022)
                                    ->setDr2022($dr2022)
                                    ->setNumConc2022($numConc2022)
                                    ->setCodConc($codConc)
                                    ->setCodConcDr($codConcDr)
                                    ->setCodConClo($codConClo)
                                    // ->setDescBatit($descBatit)
                                    // ->setTypMen($typMen)
    
                                    ->setUpdateAt(new \Datetime())
                                    ->setOpSaisi($this->getUser())
    
                                    // 12/06/2022
                                    ->setZs($zs)
                                    ->setZc($zc)
                                    ->setDr($dr)
    
                                    ->setLoginAgent($loginAgent)
                                    ->setTypeHabitat($typeHabitat)
                                    ->setAutreHabitat($autreHabitat)
                                    ->setTypeInfrastructure($typeInfrastructure)
                                    ->setNombreHabitation($nbrHabitation)
                                    ->setCaseId($caseId)
    
                                    ->setFichier($sheetCentroide);
    
                                $defaultEntityManager->persist($isConcession);
    
                                $countUpdating++;
                            }
                        } else if(empty($codQvh2022) && (!empty($objectId) || !empty($codDr2022)) ){
                            $isCpy = $filesystem->exists([$filedest . "/" . $name]);
                            if ($isCpy) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                            }
                            $defaultEntityManager->rollback();
            
                            return $this->json("COD_QVH_2022 VIDE: IDIFICE = ". $objectId . " et COD_DR_2022:" . $codDr2022);
                            
                        } else if(empty($codQvh2022) && empty($objectId) && empty($codDr2022) ){
                            $isCpy = $filesystem->exists([$filedest . "/" . $name]);
                            if ($isCpy) {
                                Utils::deleteExcelTmpFile($name, $filedest . "/");
                            }
                            $defaultEntityManager->rollback();
            
                            return $this->json("Vérifier s'il n'y a pas de lignes vides à la fin des enregistrements");
                        }
                    }
    
                    // $isCpy = $filesystem->exists([$filedest . "/" . $name]);
    
                    // if ($isCpy) {
                    // if ($isCpy && ($countSaving == count($sheetData) || $countUpdating == count($sheetData))) {
                    $defaultEntityManager->flush();
                    $defaultEntityManager->commit();
    
                    // return $this->json('Le fichier ' . $name . ' a été ' . $label . ' sur le serveur', 200);
                    return $this->json('Le fichier ' . $name . ' a été ' . $label . ' sur le serveur. ' . $countSaving . ' Conc ajouté(s) et ' . $countUpdating . ' Conc ont été mis à jour !', 200);
                    // }
                // } catch (UniqueConstraintViolationException $e) {
                } catch (\Exception $th) {
                    $defaultEntityManager->getConnection()->rollBack();
                    return $this->json($th->getMessage());
                } catch (\Exception $th) {
                    $isCpy = $filesystem->exists([$filedest . "/" . $name]);
                    if ($isCpy) {
                        Utils::deleteExcelTmpFile($name, $filedest . "/");
                    }
                    $defaultEntityManager->rollback();
                    // return $this->json($th->getMessage(), 500);
    
                    $nbRow = $repo->findBy(['longitude' => $lon, 'latitude' => $lat]);
                    if (count($nbRow) > 0) {
                        return $this->json("Tentative d'ajout de doublons de Coordonnées GPS [lat:" . $lat . " lon:" . $lon . "]");
                    }
    
                    return new JsonResponse($th->getMessage() . " |ID_EDIFICE=" . $objectId . " |COD_QVH_2022=" . $codQvh2022 . " |COD_DR_2022=" . $codDr2022 . " |LOGIN_AGENT=" . $loginAgent, 500);
                }
            }
    
            if ($isCpy) {
                Utils::deleteExcelTmpFile($name, $filedest . "/");
                return $this->json('Echec copie sur le serveur du fichier ' . $name, 500);
            }
    
            return $this->json('Echec envoi du fichier ' . $name . ' vers le serveur', 500);
        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), 500);
        }
    }

    /**
     * Télécharger un fichier Excel en renseignant le nom et le path 
     * 
     * @Route("/sheet/{name}/download", name="app_sheet_file_download", options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function downlaodSheetFile(Request $request, SheetCentroidesRepository $sheetCentroidesRepository, \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL)
    {
        $path = "";
        $sheetName = $request->get("name");
        $isSheetExists = false;

        $path = $this->getSheetDirectory($request->get("type"));

        if (empty($path)) {
            return new Response("<center style='padding-top:250px;'><h1>Folder <strong style='color: red;'>" . $path . "</strong> introuvable</h1></center>");
        } else {
            $isSheetExists = $this->filesystem->exists([$path . "/" . $sheetName]);
        }

        if ($isSheetExists == false) {
            $sheet = $sheetCentroidesRepository->findOneBy(['fileName' => $sheetName]);
            if ($sheet) {
                $defaultEntityManager->remove($sheet);
                $defaultEntityManager->flush();
            }
            return new Response("<center style='padding-top:250px;'><h1>Fichier <strong style='color: #2b4978;'>" . $sheetName . "</strong> introuvable</h1></center>");
        }

        return $this->file($path . "/" . $sheetName, $sheetName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * Suppression multiple de sheets
     *
     * @Route("/sheets/removes", name="app_remove_multiple_sheets")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function removeMultipleSheets(
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        Request $request,
        SheetCentroidesRepository $sheetCentroidesRepository,
        CentroideDrZsZcRepository $repo,
        CentroideZsRepository $zsRepo,
        CentroideZcRepository $zcRepo,
        CentroideQvhRepository $qvhRepo,
        CentroideDrRepository $drRepo,
        CentroideParcellairesDrRepository $parcellaireRepo
    ): JsonResponse {
        $files = $request->get('files');

        $filesystem = new Filesystem();

        $path = $this->getSheetDirectory($request->get("_type"));

        if (empty($path)) {
            return $this->json("folder " . $path . " is not found", 500);
        }

        $countDeleting = 0;

        try {
            $defaultEntityManager->beginTransaction();

            $typeFile = $request->get("_type");

            foreach ($files as $f) {
                $sheetFile = $path . "/" . $f;
                $isSheetFound = $this->filesystem->exists([$sheetFile]);

                if ($isSheetFound) {
                    $filesystem->remove(['symlink', $sheetFile]);
                }

                $sheet = $sheetCentroidesRepository->findOneBy(['fileName' => $f]);
                if ($sheet) {

                    if ($typeFile == "drzszc" && count($sheet->getCentroideDrZsZcs()) > 0) {
                        $repo->deleteDrZsZc($sheet->getCentroideDrZsZcs()[0]->getFichier());
                    }

                    if ($typeFile == "zs" && count($sheet->getCentroideZs()) > 0) {
                        // foreach ($sheet->getCentroideZs() as $zs) {
                        //     // Del. Parcellaires
                        //     $parcellaireRepo->deleteParcellairesByZS($zs);
                        //     $defaultEntityManager->flush();
                        //     // Del. QVH
                        //     foreach ($zs->getCentroideDrs() as $qvh) {
                        //         $qvhRepo->deleteQvhByCodQVH_2022($qvh->getCodDr2022());
                        //     }
                        //     $defaultEntityManager->flush();
                        //     // Del. DR
                        //     $drRepo->deleteDrByZS($zs->getId());
                        //     $defaultEntityManager->flush();
                        //     // Del. ZC
                        //     $zcRepo->deleteZcByZS($zs->getId());
                        //     $defaultEntityManager->flush();
                        // }
                        // Del. ZS
                        // $canDelete = true;
                        foreach ($sheet->getCentroideZs() as $zs) {
                            if (count($zs->getCentroideZcs()) > 0) {
                                // $canDelete = false;
                                return $this->json("La Zone de supervision " . $zs->getZsup() . "|" . $zs->getCodZsup() . " est lié à des ZC. Veuillez supprimer d'abord les ZC qui lui sont rattachées.", 500);
                            }
                        }
                        $zsRepo->deleteZs($sheet->getCentroideZs()[0]->getFichier());
                    }

                    if ($typeFile == "zc" && count($sheet->getCentroideZcs()) > 0) {
                        // foreach ($sheet->getCentroideZcs() as $zc) {
                        //     // Del. Parcellaires
                        //     $parcellaireRepo->deleteParcellairesByZC($zc->getId());

                        //     foreach ($zc->getCentroideDrs() as $qvh) {
                        //         // Del. qvh
                        //         $qvhRepo->deleteQvhByCodQVH_2022($qvh->getCodDr2022());
                        //         // $parcellaireRepo->deletedParcellairesByCodqDr_2022($qvh->getCodDr2022());
                        //     }
                        //     // Del. Dr
                        //     $drRepo->deleteDrByZC($zc->getId());

                        // }
                        // Del. ZC
                        foreach ($sheet->getCentroideZcs() as $zcont) {
                            if (count($zcont->getCentroideDrs()) > 0) {
                                return $this->json("La Zone de controle " . $zcont->getZcont() . "|" . $zcont->getNumZcont() . " est lié à des DR. Veuillez supprimer d'abord les ZC qui lui sont rattachées.", 500);
                            }
                        }
                        $zcRepo->deleteZc($sheet->getCentroideZcs()[0]->getFichier());
                    }

                    if ($typeFile == "qvh" && count($sheet->getCentroideQvhs()) > 0) {
                        // foreach ($sheet->getCentroideQvhs() as $qvh) {
                        //     // Del. Parcellaires
                        //     $parcellaireRepo->deletedParcellairesByCodqvh_2022($qvh->getCodQvh2022());
                        //     // Del. Qvh
                        //     $drRepo->deletedDrByCodqvh_2022($qvh->getCodQvh2022());
                        // }
                        // Del. QVH
                        foreach ($sheet->getCentroideQvhs() as $qvh) {
                            $dt = $parcellaireRepo->findBy(['codQvh2022' => $qvh->getCodQvh2022()]);
                            if (count($dt) > 0) {
                                return $this->json("Ce COD_QVH_2022 " . $qvh->getQvh2022() . "|" . $qvh->getCodQvh2022() . " est lié à des Concessions. Veuillez supprimer d'abord les Cpncessions qui lui sont rattachées.", 500);
                            }
                        }
                        $qvhRepo->deleteQvh($sheet->getCentroideQvhs()[0]->getFichier());
                    }

                    if ($typeFile == "dr" && count($sheet->getCentroideDrs()) > 0) {
                        foreach ($sheet->getCentroideDrs() as $dr) {
                            // Del. Parcellaires
                            // $parcellaireRepo->deletedParcellairesByCodqvh_2022($dr->getCodDr2022());
                            $dt = $parcellaireRepo->findBy(['codDr2022' => $dr->getCodDr2022()]);
                            if (count($dt) > 0) {
                                return $this->json("Ce CodDr_2022 " . $dr->getCodDr2022() . "|" . $dr->getDr2022() . " est lié à des Concessions. Veuillez supprimer d'abord les Cpncessions qui lui sont rattachées.", 500);
                            }
                        }
                        $drRepo->deleteDr($sheet->getCentroideDrs()[0]->getFichier());
                    }

                    if ($typeFile == "centroide" && count($sheet->getCentroideParcellairesDrs()) > 0) {
                        $parcellaireRepo->deleteParcellaires($sheet->getCentroideParcellairesDrs()[0]->getFichier());
                    }

                    $defaultEntityManager->remove($sheet);
                    $countDeleting++;
                }
            }

            $defaultEntityManager->flush();
            $defaultEntityManager->commit();
        } catch (\Exception $th) {
            $defaultEntityManager->rollback();
            return $this->json("csw :" + $th->getMessage(), 500);
        }

        return $this->json($countDeleting . " fichiers ont été supprimés avec succès !", 200);
    }


    // TODO: Récupère le répertoire source 
    public function getSheetDirectory(String $label = ""): string
    {
        if ($label == 'centroide') {
            return $this->getParameter('sheetCentroides');
        } else  if ($label == 'dr') {
            return $this->getParameter('sheetDrs');
        } else  if ($label == 'qvh') {
            return $this->getParameter('sheetQvhs');
        } else  if ($label == 'zs') {
            return $this->getParameter('sheetZs');
        } else  if ($label == 'zc') {
            return $this->getParameter('sheetZc');
        } else if ($label == 'drzszc') {
            return $this->getParameter('sheetDrZsZc');
        }

        return "";
    }

    /**
     * @Route("/pv/upload", name="app_pv")
     * @IsGranted("ROLE_USER")
     */
    public function pvIndex(PvRepository $pvRepo): Response
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $pv = $pvRepo->findOneBy(['commission' => $this->getUser()]);

        return $this->render('candidatures/pv_upload.html.twig', ['pv' => $pv]);
    }

    /**
     * 
     * @Route("/pv/upload/{id}", name="app_departement_pv_upload", options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     * @param Request $request
     * @throws \Exception
     */
    public function pvUpload(PvRepository $pvRepo, Departements $departements, \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $filedest = $this->getParameter('pvPath');
        $isCpy = false;

        $name = "";
        $path = "";
        if (!empty($_FILES["file"]["name"])) {
            $name = $_FILES["file"]["name"];
            $path = $_FILES['file']['tmp_name'];
            $extension = substr(strrchr($name, '.'), 1);

            if (!file_exists($filedest)) {
                mkdir($filedest, 0777, true);
            }

            try {
                $namePv = $this->getUser()->getDepartement()->getCode();
                $fileName = "pv_commission_" . $namePv . "." . $extension;
                move_uploaded_file($path, $filedest . "/" . $fileName);
            } catch (FileException $e) {
                // dd($e);
            }


            try {

                $pv = $pvRepo->findOneBy(['commission' => $this->getUser()]);
                if ($pv) {
                    $pv->setUpdateAt(new \DateTime())
                        ->setPv($fileName);
                } else {
                    $pv = new Pv();
                    $pv->setCommission($this->getUser())
                        ->setCreateAt(new \Datetime())
                        ->setPv($fileName);
                }
                $defaultEntityManager->persist($pv);
                $defaultEntityManager->flush($pv);
            } catch (\Exception $th) {
                return $this->json("Le PV a été copié sur le serveur avec succès");
            }
        }

        return $this->json("Le PV a été copié sur le serveur avec succès");
    }

    /**
     * 
     * @Route("/pv/{id}/download", name="app_pv_download", options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     */
    public function downloadPV(Pv $pv)
    {
        $sheetName = $pv->getPv();

        $path = $this->getParameter("pvPath");

        if (!file_exists($path . "/" . $sheetName)) {
            return new Response("<center style='padding-top:250px;'><h1><strong style='color: #2b4978;'>il n'y a aucun PV chargé pour cette commission</h1></center>");
        }

        return $this->file($path . "/" . $sheetName, $sheetName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * Permet de consulter le contenu du fichier
     * 
     * @Route("/sheets/centroide/{id}/file-content", name="app_sheets_centroide_file_content", options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function detail(
        SheetCentroides $fichier,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        CentroideDrRepository $repo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ): Response {

        set_time_limit(0);

        $breadcrumbs->addRouteItem("Centroîde contenu", "app_sheets_centrodies");
        $breadcrumbs->addRouteItem("Détail", "app_sheets_centroide_file_content", ['id' => $fichier->getId()]);

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL,  $fichier->getId());

            $zs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $zs->getTotalItemCount(),
                    'data' => $zs->getItems()
                ]
            );
        }

        return $this->render('sheets/details/conc.html.twig', ['fichier' => $fichier]);
    }
}

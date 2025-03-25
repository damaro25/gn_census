<?php

namespace App\Controller;

use ZipArchive;
use App\Entity\User;
use App\PDF\PigorPdf;
use App\Entity\Regions;

use App\Remote\CopieCsdb;
use App\Entity\Departements;
use App\Repository\PvRepository;
use App\Repository\UserRepository;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Repository\SalaireBaseRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Controller\CandidaturesController;
use App\Repository\CandidaturesRepository;
use App\Repository\DepartementsRepository;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\CommunesArrCommunautesRurales;
use App\Entity\MapUserPaid;
use App\Repository\CommissionInfosRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CandidaturesAticRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CentroideParcellairesDrRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Repository\CommunesArrCommunautesRuralesRepository;
use App\Repository\CompositionRepository;
use App\Repository\DistrictsRepository;
use App\Repository\DrEpcRepository;
use App\Repository\EpcAgentsRepository;
use App\Repository\MapUserPaidRepository;
use App\Repository\RemplacementArCollecteRepository;
use App\Repository\SallesRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportDataController extends AbstractController
{
    private $defaultEntityManager;
    private $kernel;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL, \Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        // $this->filesystem = new Filesystem();
        $this->defaultEntityManager = $defaultEntityManager;
        $this->kernel = $kernel;
    }

    function resizeCells($spreadsheet)
    {
        // $sheet->getRowDimension('1')->setRowHeight(-1);
        // $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(30); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(40); // col. 
        $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(30); // col. 
    }

    private function getTableColumns(String $tableName)
    {
        // $em is your Doctrine\ORM\EntityManager instance
        $columnNames = [];

        try {
            $schemaManager = $this->defaultEntityManager->getConnection()->getSchemaManager();
            // array of Doctrine\DBAL\Schema\Column
            $columns = $schemaManager->listTableColumns($tableName);


            foreach ($columns as $column) {
                $columnNames[] = $column->getName();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $columnNames;
        // $columnNames contains all column names
    }


    public function sqlRequest($db, $table, $columns, $by, $liste): array
    {
        $resulats = [];

        try {
            //code...
            $cartoConn = $this->defaultEntityManager->getConnection();

            $sql = "SELECT " . $columns . " FROM " . $db . $table . " WHERE " . $by . " IN " . $liste . ";";
            if ($by == '' || $liste == '()') {
                $sql = "SELECT " . $columns . " FROM " . $db . $table . ";";
            }
            // var_dump($sql); die;

            $stmt = $cartoConn->prepare($sql);
            $resulats = $stmt->executeQuery()->fetchAllAssociative();

            return (array) $resulats;
        } catch (\Throwable $th) {
            //throw $th;
        }
        return $resulats;
    }


    /**
     * @Route("/tracking/days", name="app_export_tracking_day", methods={"GET", "POST"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
    */
    public function exportApplicationStatus(DistrictsRepository $repo, Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $candidats = $repo->findBy([], ['fdcode' => 'ASC']);

        $dt = new \DateTime();
        $fileName = "applicationStatus_" . $dt->format("d_m_Y_H_i") . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportSheet($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    private function exportSheet($districts, string  $baseUrl, $sheet)
    {

        $myVariableCSV = "LGA;DISTRICT;COD_DISTRICT;NUMBER OF APPLICANTS;EXPECTED NUMBER;PERCENTAGE (%);";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(30);
        }

        $sheet->setCellValue('A1', 'LGA');
        $sheet->setCellValue('B1', 'DISTRICT');
        $sheet->setCellValue('C1', 'COD_DISTRICT');
        $sheet->setCellValue('D1', 'NBR. APPLICANTS');
        $sheet->setCellValue('E1', 'NBR. EXPECTED');
        $sheet->setCellValue('F1', 'PERCENTAGE (%)');


        //add datas 
        $i = 2;
        foreach ($districts as $dstr) {

            $lga = $dstr->getLga();

            $lga = $lga->getName();
            $name = $dstr->getName();
            $code = $dstr->getFdcode();
            $nbrApplicants = count($dstr->getApplications());
            $nbrExpected = $dstr->getNbEnumExpected();

            $percentage = round(intval($dstr->getPercentageCandidature()), 2);

            $myVariableCSV = "$lga|$name|$code|$nbrApplicants|$nbrExpected|$percentage";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }
        return $sheet;
    }

    #[Route('/candidatures/export-cacrs', name: 'app_candidats_export_cacrs', methods: ['POST'], options: ['expose' => true])]
    public function ExportcandidatureByCacr(
        CandidaturesController $candidatController,
        CandidaturesRepository $repo,
        Request $request,
        UserRepository $usersRepo
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $me = $usersRepo->findOneBy(["id" => $this->getUser()]);

        $arrndCacrs = [];
        if ($me->getCustomArrnd()) {
            $homeWorks = $me->getCustomArrnd()->getCustomArrondissementCommunes();
            $arrndCacrs =  array_map(function ($cacr) {
                return $cacr->getCacr()->getId();
            }, $homeWorks->toArray());
        }


        $candidats = $repo->findUnSelectedCandidatsByCodes($request->get('cacrs'), $arrndCacrs);

        if (count($candidats) == 0) {
            return new Response("<center style='padding-top:250px;'><h1>Il n'y a aucun postulant (candidats non encore sélectionnés) pour les districts sélectionnées</center>");
        }

        $fileName = "Listes_des_candidats_non_selectionnes.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $candidatController->exportExcelCandidature($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    #[Route('/export/accounts/{profil}', name: 'app_export_account', methods: ['GET'], options: ['expose' => true])]
    public function exportAccounts(UserRepository $repo, Request $request, $profil)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $users = $repo->findUserByRoles($profil);

        $dt = new \DateTime();
        $fileName = "accounts_" . $profil . '_' . $dt->format("d_m_Y_H_i") . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->accountSheet($users, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    private function accountSheet($users, string  $baseUrl, $sheet)
    {

        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "REGION;DEPARTEMENT;ARRONDISSEMENT;PRENOM;NOM;LOGIN;MOT DE PASSE;";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(30);
        }

        $sheet->setCellValue('A1', 'REGION');
        $sheet->setCellValue('B1', 'DEPARTEMENT');
        $sheet->setCellValue('C1', 'ARRONDISSEMENT');
        $sheet->setCellValue('D1', 'PRENOM');
        $sheet->setCellValue('E1', 'NOM');
        $sheet->setCellValue('F1', 'LOGIN');
        $sheet->setCellValue('G1', 'MOT DE PASSE');

        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($users as $user) {

            $lga = $user->getDepartement()->getRegion()->getNom();
            $departement = $user->getDepartement()->getNom();
            $arrnd = $user->getCustomArrnd() ?  $user->getCustomArrnd()->getNom() : "";

            $name = $user->getNom();
            $prenom = $user->getPrenom();
            $login = $user->getEmail();
            $password = $user->getPasswordView();


            $myVariableCSV = "$lga|$departement|$arrnd|$name|$prenom|$login|$password";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
            }

            $i++;
        }
        return $sheet;
    }


    #[Route('/atics/suvi-day', name: 'app_candidature_atics_tracking_export', methods: ['GET'], options: ['expose' => true])]
    public function exportSituationJour(DepartementsRepository $repo, Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $departements = $repo->findBy([], ['code' => 'ASC']);

        $dt = new \DateTime();
        $fileName = "ListCandidatureAssistantsTIC_" . $dt->format("d_m_Y_H_i") . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelAtics($departements, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    // Permet d'exporter les Atics
    public function exportExcelAtics($departements, string  $baseUrl, $sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "REGION;DEPARTEMENT;CODE_DEPT;ARRONDISSEMEENT;NBR_POSTULANTS;NBR_ATTENDUS;POURCENTAGE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G',];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if ($key == 2) {
                $sheet->getColumnDimension($col)->setWidth(18);
            } else {
                $sheet->getColumnDimension($col)->setWidth(20);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($departements as $dept) {
            if ($dept->isIsRegion()) {
                foreach ($dept->getCustomArrondissements() as $arrnd) {
                    $lga = $dept->getRegion()->getNom();
                    $departement = $dept->getNom();
                    $departementCode = $dept->getCode();
                    $nbrApplicants = count($arrnd->getCandidaturesAtics());
                    $arrdName = $arrnd->getNom();
                    $nbrExpected = 3;
                    $percentage = 0;

                    if ($nbrApplicants > 0) {
                        $result = ($nbrApplicants * 100) / $nbrExpected;
                        $percentage = round($result, 2);
                    }

                    // PUT
                    $myVariableCSV = "$lga|$departement|$departementCode|$arrdName|$nbrApplicants|$nbrExpected|$percentage";
                    $mesValeurs = explode('|', $myVariableCSV);
                    for ($x = 0; $x < count($mesValeurs); $x++) {
                        if ($x == 1) {
                            $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                        } else if ($x == 2) { // nin
                            $sheet->setCellValue($colonnesExcel[$x] . $i,  $mesValeurs[$x]);
                            $tailleCni =  strlen($mesValeurs[$x]);

                            $sheet->getStyle($colonnesExcel[$x] . $i)->getNumberFormat()->setFormatCode(str_repeat('0', $tailleCni));
                        } else {
                            $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                        }
                    }

                    $i++;
                }
            } else {
                foreach ($dept->getCommunesArrondissementsVilles() as $arrnd) {
                    $lga = $dept->getRegion()->getNom();
                    $departement = $dept->getNom();
                    $departementCode = $dept->getCode();
                    $nbrApplicants = count($arrnd->getCandidaturesAtics());
                    $nbrExpected = 3;
                    $percentage = 0;
                    $arrdName = $arrnd->getNom();

                    if ($nbrApplicants > 0) {
                        $result = ($nbrApplicants * 100) / $nbrExpected;
                        $percentage = round($result, 2);
                    }

                    $myVariableCSV = "$lga|$departement|$departementCode|$arrdName|$nbrApplicants|$nbrExpected|$percentage";
                    $mesValeurs = explode('|', $myVariableCSV);
                    for ($x = 0; $x < count($mesValeurs); $x++) {
                        if ($x == 1) {
                            $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                        } else if ($x == 2) { // nin
                            $sheet->setCellValue($colonnesExcel[$x] . $i,  $mesValeurs[$x]);
                            $tailleCni =  strlen($mesValeurs[$x]);

                            $sheet->getStyle($colonnesExcel[$x] . $i)->getNumberFormat()->setFormatCode(str_repeat('0', $tailleCni));
                        } else {
                            $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                        }
                    }

                    $i++;
                }
            }
        }
        return $sheet;
    }

    #[Route('/atics/export-national', name: 'app_export_atics_national', methods: ['GET'], options: ['expose' => true])]
    public function aticsExport(
        Request $request,
        CandidaturesAticRepository $aticRepo
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $fileName = "AssistantsTICs_RGPH5_2023.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $candidats = $aticRepo->findBy([], ['score' => 'DESC']);

        $this->exportAtics($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }


    #[Route('/template/membres-commission', name: 'app_export_template_membre_commission', methods: ['GET'], options: ['expose' => true])]
    public function templateMembreCommission(UserRepository $repo): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // $commissions = "";
        // $srsd = $repo->findOneBy(["id" => $this->getUser()]);
        // foreach ($srsd->getDepartement()->getRegion()->getDepartements() as $dept) {
        //     if (!$dept->isIsRegion()) {
        //         $commissions .= $dept->getNom() . ",";
        //     } else {
        //         foreach ($dept->getCustomArrondissements() as $cust) {
        //             $commissions .= $cust->getNom() . ",";
        //         }
        //     }
        // }

        // $commissions = strlen($commissions) > 0 ? substr($commissions, 0, strlen($commissions) - 1) : "";
        // dd($commissions);
        $connectedUser = $repo->findOneBy(['id' => $this->getUser()]);

        $commissions = $connectedUser->getCustomArrnd() != NULL
            ? $connectedUser->getCustomArrnd()->getNom()
            : $connectedUser->getDepartement()->getNom();

        $fileName = "TemplateMembresCom.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        // $candidats = $aticRepo->findBy([], ['score' => 'DESC']);

        $this->initializeTemplate($sheet, $commissions);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function initializeTemplate($sheet, $comNames)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "COMMISSION;PRENOM;NOM;SEXE;EMAIL;TELEPHONE;CNI;MOYEN_PAIEMENT_MOBILE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', "H",];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [3])) {
                $sheet->getColumnDimension($col)->setWidth(8);
            } else {
                $sheet->getColumnDimension($col)->setWidth(25);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        /**
         * Set cell B3 with the "Select from the drop down options:"
         * string value, will serve as the 'Select Option Title'.
         */
        // $sheet->setCellValue('B3', 'Select from the drop down options:');
        $this->setCellDropDown($sheet, 'D', '"Homme, Femme"', 'Sélectionnez le sexe sur la liste déroulante');
        $this->setCellDropDown($sheet, 'H', '"Wave, Orange Money, Free Money"', 'Définissez le moyen de paiement souhaité par cette personne définie sur la liste déroulante ');
        $this->setCellDropDown($sheet, 'A', '"' . $comNames . '"', 'Chosissez la commission auquelle siégera la personne');

        return $sheet;
    }

    private function setCellDropDown($sheet, $celulle, $dropDownValues, $note = "")
    {
        /**
         * Set the 'drop down list' validation on C3.
         */
        $validation = $sheet->getCell($celulle . '2')->getDataValidation();
        $validation->setSqref($celulle . '2:' . $celulle . '148576');

        /**
         * Since the validation is for a 'drop down list',
         * set the validation type to 'List'.
         */
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);

        /**
         * List drop down options.
         */
        $validation->setFormula1($dropDownValues);

        /**
         * Do not allow empty value.
         */
        $validation->setAllowBlank(false);

        /**
         * Show drop down.
         */
        $validation->setShowDropDown(true);

        /**
         * Display a cell 'note' about the
         * 'drop down list' validation.
         */
        $validation->setShowInputMessage(true);

        /**
         * Set the 'note' title.
         */
        $validation->setPromptTitle('Note');

        /**
         * Describe the note.
         */
        $validation->setPrompt($note);

        /**
         * Show error message if the data entered is invalid.
         */
        $validation->setShowErrorMessage(true);

        /**
         * Do not allow any other data to be entered
         * by setting the style to 'Stop'.
         */
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);

        /**
         * Set descriptive error title.
         */
        $validation->setErrorTitle('Invalid option');

        /**
         * Set the error message.
         */
        $validation->setError('Select one from the drop down list.');
    }

    #[Route('/template/appui-rh/{id}', name: 'app_export_template_appui_rh', methods: ['GET'], options: ['expose' => true])]
    public function templateAppuiRH(Regions $lga, DepartementsRepository $repo): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $commissions = "";
        // $departements = $repo->findBy([], ['code' => 'ASC']);
        foreach ($lga->getDepartements() as $dept) {
            if (!$dept->isIsRegion()) {
                $commissions .= $dept->getNom() . ",";
            } else {
                foreach ($dept->getCustomArrondissements() as $cust) {
                    $commissions .= $cust->getNom() . ",";
                }
            }
        }

        $commissions = strlen($commissions) > 0 ? substr($commissions, 0, strlen($commissions) - 1) : "";
        // dd($commissions);
        // KOLDA,VELINGARA,MEDINA YORO FOULAH,MATAM,KANEL,RANEROU FERLO,KAFFRINE,BIRKELANE,KOUNGHEUL,MALEM HODDAR,KEDOUGOU,SALEMATA,SARAYA,SEDHIOU,BOUNKILING,GOUDOMP
        // $commissions = "ALMADIES,DAKAR-PLATEAU,GRAND DAKAR,PARCELLES ASSAINIES,PIKINE DAGOUDANE,THIAROYE,DIAMNIADIO,RUFISQUE EST,SANGALKAM,SAM NOTAIRE,WAKHINANE NIMZATT,YEUMBEUL NORD,MALIKA,JAXAAY,BIGNONA,OUSSOUYE,ZIGUINCHOR,MBOUR,THIES,TIVAOUANE,KEBEMER,LINGUERE,LOUGA,FATICK,FOUNDIOUGNE,GOSSAS";

        $fileName = "TEMPLATE_AppuisRH_REGION_" . $lga->getNom() . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        // $candidats = $aticRepo->findBy([], ['score' => 'DESC']);

        $this->initAppuiRhSheet($sheet, $commissions);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function initAppuiRhSheet($sheet, $comNames)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "COMMISSION;PRENOM;NOM;SEXE;EMAIL;TELEPHONE;CNI";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [3])) {
                $sheet->getColumnDimension($col)->setWidth(8);
            } else {
                $sheet->getColumnDimension($col)->setWidth(25);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        /**
         * Set cell B3 with the "Select from the drop down options:"
         * string value, will serve as the 'Select Option Title'.
         */
        // $sheet->setCellValue('B3', 'Select from the drop down options:');
        $this->setCellDropDown($sheet, 'A', '"' . $comNames . '"', "Chosissez la commission auquelle l'agent d'Appui RH siégera");
        $this->setCellDropDown($sheet, 'D', '"Homme, Femme"', 'Sélectionnez le sexe sur la liste déroulante');

        return $sheet;
    }

    #[Route('/export/commission_members/{id}', name: 'app_export_com_members', methods: ['GET'], options: ['expose' => true])]
    public function exportComMembers(User $appuiRH, CommissionInfosRepository $repo, Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $comName = $appuiRH->getCustomArrnd() != NULL
            ? $appuiRH->getCustomArrnd()->getNom()
            : $appuiRH->getDepartement()->getNom();

        $membres = $repo->findBy(['commission' => $comName], ['moyenPaiement' => 'ASC']);

        $dt = new \DateTime();
        $fileName = "MEMBRES_COM_" . $comName . '_' . $dt->format("d_m_Y_H_i") . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportComMembersSheet($membres, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    #[Route('/pv/commission/{id}/export', name: 'app_procesverbal_commission_export', methods: ['GET'], options: ['expose' => true])]
    public function pvCandidatureSelectionnerByDepartement(
        User $connected,
        CandidaturesRepository $repo,
        CommunesArrCommunautesRuralesRepository $cacrsRepo,
        KernelInterface $kernel,
        UserRepository $userRepository
    ) {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $departement = $connected->getDepartement();
        $nomCommission = "";
        $retenu = "";
        $ledept = "";
        $cacrsStringArray = [];

        // dd($connected);

        if ($connected->getCustomArrnd() == NULL) {
            $nomCommission = "POUR LE DEPARTEMENT DE <br/>" . $departement->getNom();
            $retenu = " <br/>Département/Arrondissement de " . $departement->getNom();
            $ledept = "le département de " . $departement->getNom();
        } else  if ($connected->getCustomArrnd() != NULL) {
            $nomCommission = "POUR L'ARRONDISSEMENT DE " . $connected->getCustomArrnd()->getNom();
            $retenu = " <br/>Département/Arrondissement de " . $connected->getCustomArrnd()->getNom();
            $ledept = "Département/Arrondissement de " . $connected->getCustomArrnd()->getNom();
            $district =  $connected->getCustomArrnd()->getNom();

            if ($connected->getCustomArrnd()) {
                $homeWorks = $connected->getCustomArrnd()->getCustomArrondissementCommunes();
                $cacrsStringArray =  array_map(function ($cacr) {
                    return $cacr->getCacr()->getCode();
                }, $homeWorks->toArray());
            }
        }

        $pdf = new PigorPdf($kernel, 'PROCES-VERBAL DE RECRUTEMENT DES AGENTS RECENSEURS DANS LE CADRE <br/> DU RGPH-5, 2023');
        $pdf->addTable([], [],  "", "Procès-verbal de recrutement des agents recenseurs 
        dans le cadre du RGPH-5, 2023.", "");

        $regionName = str_pad($departement->getRegion()->getNom(), 5, ".", STR_PAD_BOTH);
        $departementName = str_pad($departement->getNom(), 5, ".", STR_PAD_BOTH);
        $an = date('Y');
        switch ($an) {
            case 2022:
                $an = "deux mille vingt-deux";
                break;
            case 2023:
                $an = "deux mille vingt-trois";
                break;
            case 2024:
                $an = "deux Mille vingt-quatre";
                break;
            default:
                # code...
                break;
        }

        $pCommune = "";
        if ($connected->getCustomArrnd() != NULL) {
            $pCommune = "<p class='c25'> <b>Arrondissement :</b> $district</p>";
        }

        $html = <<<EOD
            <style> p { font-size: 13px;}</style>
            <table>
                <tr>
                    <td colspan="1" rowspan="1">
                        <p> <b>Région </b>: $regionName </p>
                        <p class="c25"> <b>Département :</b> $departementName</p>
                        $pCommune
                    </td>
                </tr>
            </table>
            <br>
            <br>
            <hr>
        EOD;
        $pdf->writeHTML($html);

        $isPresentsHtml = <<<EOD
        <style> td, th { text-align: center; font-weight: bold;}</style>
            <table border="1" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <th>TITRE</th>
                    <th>PRENOM (S) ET NOM</th>
                    <th>FONCTION</th>
                </tr>
                <tr>
                    <td>Président</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td rowspan="6" style="margin"><br><br><br><br><br>Membres</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Appui RH ANSD</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        EOD;

        $html2 =  <<<EOD
            <style> p { font-size: 13px; } .mef { line-height: 220%; } </style>
            <p class="mef">L’an  {$an}, le………………………, s’est réunie la Commission de recrutement $retenu créée par arrêté n°…………. du…………………. pour procéder à la <br/> sélection des candidats au poste
             d'agent recenseur selon les critères consignés par l’Agence nationale de la Statistique et de la Démographie (ANSD).
            </p>
            <p><u>Etaient présents :</u> <br/></p>
            <p>$isPresentsHtml</p>
        EOD;
        $pdf->writeHTML($html2);

        $cacrsDept = [];
        $nomfichier = "";

        if ($connected->getCustomArrnd() == NULL) {
            $cacrsDept = $cacrsRepo->findDeptCacrs($departement->getCode());
            $nomfichier = 'PV_DES_CANDIDATS_DE_LA_COMMISSION_DEPT_' . $departement->getNom() . "_" . $departement->getCode();
        } else {
            $cacrsDept = $cacrsRepo->findCacrByCodes($cacrsStringArray);
            $nomfichier = 'PV_DES_CANDIDATS_DE_LA_COMMISSION_COMMUNE_Arrd_' . $connected->getCustomArrnd()->getNom();
        }

        foreach ($cacrsDept as $dstr) {
            // TODO: SELECTIONNES
            $pdf->AddPage();
            // $pdf->Footer("ANSD-RGPH-5 / Commune de ".$dstr->getNom());
            $candidats = $repo->findBy(
                [
                    // 'departement' => $departement,
                    'cacrWork' => $dstr,
                    'posteSouhaite' => 'Agents Recenseurs',
                    'isSelected' => 1,
                    // 'isReserviste' => false
                ],
                ['nom' => 'ASC']
            );

            // $candidats = $repo->listePrincipake($departement, NULL, $dstr);

            $data = [];
            $i = 1;
            foreach ($candidats as $candidat) {

                $data[] = ['<span style="text-align:center;">' . $i++ . '</span>', ucfirst($candidat->getPrenom()), strtoupper($candidat->getNom()), '<span style="text-align:center;">' . $candidat->getDateNaissance()->format('d-m-Y') . '</span>', strtoupper($candidat->getLieuNaissance()), substr_replace($candidat->getNin(), '****', -4),];
            }

            $district =  str_pad($dstr->getNom(), 5, ".", STR_PAD_BOTH);
            if ($connected->getCustomArrnd() == NULL) {
                $cav = str_pad($dstr->getCommuneArrondissementVille()->getNom(), 5, ".", STR_PAD_BOTH);
            } else {
                $cav = str_pad($connected->getCustomArrnd()->getNom(), 5, ".", STR_PAD_BOTH);
            }

            $arrndDetail = "";
            if ($connected->getCustomArrnd() != NULL) {
                $arrndDetail = " <p> <b>Arrondissement:</b> $cav </p>";
            }

            $htmlx = <<<EOD
                <style> p { font-size: 13px;}</style>
                    <table>
                        <tr>
                            <td colspan="1" rowspan="1">
                                <p> <b>Département:</b> $departementName </p>
                                $arrndDetail
                                <p> <b>Commune:</b> $district </p>
                                <p> <b><u>Liste Principale</u></b></p>
                            </td>
                        </tr>
                    </table>
            EOD;

            $pdf->writeHTML($htmlx);

            $headers = ["N°", "PRENOM", "NOM", "Date de naissance", "Lieu de Naissance", "CNI"];
            $pdf->SetY($pdf->getY() + 5);
            $pdf->addTable($headers, $data,  "", "ANSD-RGPH-5/L.P/" . $dstr->getNom());

            // TODO: LISTE ATTENTE
            $pdf->AddPage();
            // $pdf->Footer("ANSD-RGPH-5 / Commune de ".$dstr->getNom());
            $candidatsAttente = $repo->findBy(
                [
                    // 'departement' => $departement,
                    'cacrWork' => $dstr,
                    'posteSouhaite' => 'Agents Recenseurs',
                    'isReserviste' => 1
                ],
                ['score' => 'DESC']
            );

            $data = [];
            $i = 1;
            foreach ($candidatsAttente as $candidat) {

                $data[] = ['<span style="text-align:center;">' . $i++ . '</span>', ucfirst($candidat->getPrenom()), ucfirst($candidat->getNom()), '<span style="text-align:center;">' . $candidat->getDateNaissance()->format('d-m-Y') . '</span>', $candidat->getLieuNaissance(), substr_replace($candidat->getNin(), '****', -4),];
            }

            $htmlx = <<<EOD
                <style> p { font-size: 13px;}</style>
                    <table>
                        <tr>
                            <td colspan="1" rowspan="1">
                                <p> <b>Département:</b> $departementName </p>
                                $arrndDetail
                                <p> <b>Commune:</b> $district </p>
                                <p> <b><u>Liste d'Attente</u></b></p>
                            </td>
                        </tr>
                    </table>
            EOD;
            $pdf->writeHTML($htmlx);

            $headers = ["N°", "Prénom", "Nom", "Date de Naissance", "Lieu de Naissance", "CNI"];
            $pdf->SetY($pdf->getY() + 5);
            $pdf->addTable($headers, $data,  "", "ANSD-RGPH-5/L.A/" . $dstr->getNom());
            // fin
        }

        $pdf->AddPage();

        $pdf->addTable([], [],  "", "Procès-verbal de recrutement des agents recenseurs dans le cadre du RGPH-5, 2023.", "");

        $html2 =  <<<EOD
        <style> p { font-size: 13px; } .mef { line-height: 220%; } </style>
        <p class="mef">A l’issue des travaux de la Commission de recrutement, les candidats listés, ci-dessus, ont été retenus pour la formation dans $ledept.</p>
        EOD;
        $pdf->writeHTML($html2);

        $dstr =  $connected->getCustomArrnd() != NULL ?  $connected->getCustomArrnd()->getNom() :  $connected->getDepartement()->getNom();
        $dtcourant = new \Datetime();
        $df = $dtcourant->format('d/m/Y');
        $pdf->writeHTML("<br/><br/>Fait à $dstr, le $df <br/><br/>", true, false, false, false, "R");
        $pdf->writeHTML('<u style="font-size: 14px;">Ont signé :</u><br/>', true, false, false, false, "C");

        // Signataires
        $signataires = <<<EOD
        <style>  td { font-size: 14px;} th { text-align: center; font-weight: bold;}</style>
            <table border="1" border="1" cellspacing="0" cellpadding="10">
                <tr>
                    <th>TITRE</th>
                    <th>PRENOM (S) ET NOM</th>
                    <th>FONCTION</th>
                    <th>EMARGEMENT</th>
                </tr>
                <tr>
                    <td><u>Président:</u></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><u>Coodonnateur <br/>administratif <br/>départemental</u></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><u>Un Commissaire</u></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        EOD;
        $pdf->writeHTML($signataires);

        return $pdf->Output($nomfichier . '.pdf', 'I');
    }

    private function exportComMembersSheet($users, string  $baseUrl, $sheet)
    {

        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "NOM_COMMISSION;PRENOMS;NOM;CNI;TELEPHONE;MOYEN_PAIEMENT;NBR_JOURS_PRESENCE;A_APPROUVER_LE_PV";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(30);
        }

        $sheet->setCellValue('A1', 'NOM_COMMISSION');
        $sheet->setCellValue('B1', 'PRENOMS');
        $sheet->setCellValue('C1', 'NOM');
        $sheet->setCellValue('D1', 'CNI');
        $sheet->setCellValue('E1', 'TELEPHONE');
        $sheet->setCellValue('F1', 'MOYEN_PAIEMENT');
        $sheet->setCellValue('G1', 'NBR_JOURS_PRESENCE');
        $sheet->setCellValue('H1', 'A_APPROUVER_LE_PV');

        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($users as $user) {

            $commission = $user->getCommission();

            $name = $user->getMemberCom()->getNom();
            $prenom = $user->getMemberCom()->getPrenom();
            $nin = $user->getMemberCom()->getCni();
            $phone = $user->getMemberCom()->getTelephone();
            $moyenPaiement = $user->getMoyenPaiement();
            $joursPresence = $user->getJourPresence();
            $aSigner = $user->isIsSignPv() ? "OUI" : "NON";

            $myVariableCSV = "$commission|$prenom|$name|$nin|$phone|$moyenPaiement|$joursPresence|$aSigner";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 3) {
                    $sheet->setCellValue($colonnesExcel[$x] . $i,  $mesValeurs[$x]);
                    $tailleCni =  strlen($mesValeurs[$x]);

                    $sheet->getStyle($colonnesExcel[$x] . $i)->getNumberFormat()->setFormatCode(str_repeat('0', $tailleCni));
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }

        $this->setCellDropDown($sheet, 'H', '"OUI, NON"', 'A t-il approuver le PV ?');
        $this->setCellDropDown($sheet, 'F', '"Wave, Orange Money, Free Money"', 'Définissez le moyen de paiement souhaité par cette personne définie sur la liste déroulante ');

        return $sheet;
    }


    #[Route('/export-filter-data', name: 'filter_export_data')]
    public function filtre_donnee_export(Request $request): Response
    {
        $table = $request->get('table');
        $colonne = $request->get('col');
        // dd($table, $colonne);
        $resulats = [];

        try {
            $cartoConn = $this->defaultEntityManager->getConnection();

            $sql = "SELECT DISTINCT " . $colonne . " FROM " . "dbo." . $table . ";";

            $stmt = $cartoConn->prepare($sql);
            $resulats = $stmt->executeQuery()->fetchAllAssociative();

            // return (array) $resulats;
        } catch (\Throwable $th) {
            throw $th;
        }
        // return $resulats;

        return  new JsonResponse($resulats);
    }

    #[Route('/template/assistantsTics', name: 'app_export_template_tics', methods: ['GET'], options: ['expose' => true])]
    public function getTemplateATICs(): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $fileName = "TEMPLATE_250_ATICS_RGPH5.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->initSheetTICs($sheet);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function initSheetTICs($sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "NUMERO_DOSSIER;LISTE_APPARTENANCE";
        $colonnesExcel = ['A', 'B'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [0])) {
                $sheet->getColumnDimension($col)->setWidth(20);
            } else {
                $sheet->getColumnDimension($col)->setWidth(20);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        $this->setCellDropDown($sheet, 'B', '"Principale, Suppléant"', 'Définir la liste sur laquelle le candidat a été sélectionné');

        return $sheet;
    }

    // TODO URGENCE Docteur MANE
    #[Route('/export250-atic', name: 'app_export_250_atics', methods: ['GET'], options: ['expose' => true])]
    public function export250ATIC(CandidaturesAticRepository $repo, Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);


        $selectionnesList = $repo->findBy(['isSelected' => 1], ['lga' => 'ASC']);

        $dt = new \DateTime();
        $fileName = "LISTE_250_ATICs_SELECTIONNES_RGPH5_" . $dt->format("d_m_Y_H_i") . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportAticSheetFormat($selectionnesList, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }


    /**
     * 
     * @Route("/etatpaid-dept", name="app_export_etat_paid_dept_zip")
     * @param Request $request
     * @throws \Exception
     */
    public function buildEtatPaid(
        SalaireBaseRepository $salaireRepository,
        CandidaturesRepository $repo,
        KernelInterface $kernel,
        DepartementsRepository $deptRepo
    ) {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);


        $destination = $kernel->getProjectDir() . '/var/etatPaid_zip';

        $filesNames = array();

        $filesystem = new Filesystem();

        // supprime le dossier créer
        try {
            if (!file_exists(dirname($destination))) {
                $filesystem = new Filesystem();
                $filesystem->mkdir($destination, 0777);
            } else {
                $filesystem->remove([$destination]);

                $filesystem = new Filesystem();
                $filesystem->mkdir($destination, 0777);
            }
        } catch (\Exception $th) {
            //throw $th;
        }

        $departements = $deptRepo->findBy([], ['code' => 'ASC']);

        $dt = new \DateTime();
        $basename = '/ETAT_PAIEMENT_DEPTS_' . $dt->format('d_m_Y_H_i') . '_' . uniqid() . '.zip';

        foreach ($departements as $dept) {

            $candidats = $repo->findBy(['departement' => $dept]);

            $fileName = "ETAT_PAID_" . $dept->getNom() . "_" . $dept->getCode() . ".xlsx";
            $spreadsheet = new Spreadsheet();
            $writer = new Xlsx($spreadsheet);

            $sheet = $spreadsheet->getActiveSheet();

            $sheets = $this->sheetEtatTemplate($candidats, $salaireRepository, $sheet, $kernel, $spreadsheet);

            if (count($candidats) > 0) {
                // $fileName = $nomFichier . '_CodDr2012_CONC_CHARGEES.xlsx';

                $temp_file = tempnam(sys_get_temp_dir(), $fileName);

                $writer->save($temp_file);
                // return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
                rename($temp_file, $destination . "/" . $fileName);

                $isFileExists = $filesystem->exists([$destination . "/" . $fileName]);

                if ($isFileExists) {
                    array_push($filesNames, $fileName);
                }
            }
        }

        // Ziper
        // $basename = '/CANDIDATS_DEPT_' . $departement->getNom() . "_" . $departement->getCode() . "_" . uniqid() . '.zip';
        $destZip = sys_get_temp_dir() . $basename;

        $allPiecesJointes =  array_map(function ($p) use ($destination) {
            return $destination . '/' . $p;
        }, $filesNames);

        CopieCsdb::createZipArchive($allPiecesJointes, $destZip);

        if (file_exists($destZip)) {
            $response =  new BinaryFileResponse($destZip);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($destZip)
            );

            // supprime le dossier créer
            if (file_exists(dirname($destination))) {
                $filesystem->remove(['symlink', $destination]);
            }

            return $response;
        } else {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }

    public function sheetEtatTemplate($candidats, $salaireRepository, $sheet, $kernel, $spreadsheet)
    {

        // $etat=$etatPaiementsRepository->findOneBy(['id' => $request->get("id")]);
        $date = new \DateTimeImmutable();
        $dateC = $date->format('d-m-Y');
        //$numero=$etat->getNumero();

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Paid');
        $drawing->setDescription('Paid');
        $drawing->setPath($kernel->getProjectDir() . '/public/dist/img/national.png'); // put your path and image here
        $drawing->setCoordinates('E1');
        $drawing->setOffsetX(100);
        $drawing->setWidthAndHeight(300, 200);
        $drawing->getShadow()->setVisible(true);
        $drawing->getShadow()->setDirection(45);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Paid');
        $drawing->setDescription('Paid');
        $drawing->setPath($kernel->getProjectDir() . '/public/dist/img/logo_grille.png'); // put your path and image here
        $drawing->setCoordinates('A5');
        $drawing->setOffsetX(100);
        $drawing->setWidthAndHeight(150, 150);
        $drawing->getShadow()->setVisible(true);
        $drawing->getShadow()->setDirection(45);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());


        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Paid');
        $drawing->setDescription('Paid');
        $drawing->setPath($kernel->getProjectDir() . '/public/dist/img/logo_rgph5.png'); // put your path and image here
        $drawing->setCoordinates('I5');
        $drawing->setOffsetX(100);
        $drawing->setWidthAndHeight(150, 150);
        $drawing->getShadow()->setVisible(true);
        $drawing->getShadow()->setDirection(45);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        $sheet->setCellValue('A1', "")->mergeCells('A1:K1');
        $sheet->setCellValue('A2', "")->mergeCells('A2:K2');
        $sheet->setCellValue('A3', "")->mergeCells('A3:K3');
        $sheet->setCellValue('A4', "")->mergeCells('A4:K4');
        $sheet->setCellValue('A6', "")->mergeCells('A6:K6');
        $sheet->setCellValue('A9', "ETAT DES FRAITS DE TRANSPORT DES CANDIDATS A LA FORMATIOON DU RECENSEMENT 2023")->mergeCells('A8:K9');
        $sheet->setCellValue('A10', "Budget : RGPH5")->mergeCells('A10:K10');
        $sheet->setCellValue('A11', "Direction de l'Administration Générale et des Ressources Humaines (DAGRH)")->mergeCells('A11:K11');

        $spreadsheet->getActiveSheet()->getStyle('A1:K1')->applyFromArray(['font' => ['bold' => true]]);
        $spreadsheet->getActiveSheet()->getStyle('A2:K2')->applyFromArray(['font' => ['bold' => true]]);
        $spreadsheet->getActiveSheet()->getStyle('A3:K3')->applyFromArray(['font' => ['bold' => true]]);
        $spreadsheet->getActiveSheet()->getStyle('A4:K4')->applyFromArray(['font' => ['bold' => true]]);
        $spreadsheet->getActiveSheet()->getStyle('A5:K5')->applyFromArray(['font' => ['bold' => True]]);
        $spreadsheet->getActiveSheet()->getStyle('A9:K9')->applyFromArray(['font' => ['bold' => true, 'size' => 17]]);
        $spreadsheet->getActiveSheet()->getStyle('A10:K10')->applyFromArray(['font' => ['bold' => true, 'size' => 17]]);

        $spreadsheet->getActiveSheet()->getStyle('A9:J9')->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A10:J10')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A13', 'N°');
        $sheet->setCellValue('B13', 'CANDIDAT');
        $sheet->setCellValue('C13', 'REGION');
        $sheet->setCellValue('D13', 'DEPARTEMENT');
        $sheet->setCellValue('E13', 'COMMUNE');
        $sheet->setCellValue('F13', 'N° Dossier');
        $sheet->setCellValue('G13', 'CNI');
        $sheet->setCellValue('H13', 'NB JOUR');
        $sheet->setCellValue('I13', 'TAUX JOURNALIER (FCFA)');
        $sheet->setCellValue('J13', 'MONTANT PERCU (FCFA)');


        $spreadsheet->getActiveSheet()->getStyle('A13:J13')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'e9e8ea'
                ]
            ],
            'font' => [
                'bold' => true,

            ]
        ]);


        $this->resizeFCells($spreadsheet);

        $writer = new Xlsx($spreadsheet);

        // incrémente le compteur des éléments non enregistrés
        $id = "";
        $nextRow = 13;
        $nextR = 0;
        $montanttotal = 0;


        $sheet->setCellValue('A12', "Dakar, Le " . $dateC)->mergeCells('A12:K12');

        try {
            foreach ($candidats as $candidat) {
                //dd($sfait);
                $nextRow++;
                $nextR++;
                $candid = $candidat->getPrenom() . ' ' . $candidat->getNom();
                $nbJour = 10;
                $lga = $candidat->getRegion()->getNom();
                $departement = $candidat->getDepartement()->getNom();
                $telephone = $candidat->getTelephone();
                $district = $candidat->getCacr()->getNom();
                $modeP = count($candidat->getSalles()) > 0 ? $candidat->getSalles()[0]->getModePaiement() : "";
                $numDoss = $candidat->getNumeroDossier();
                $nin = $candidat->getNin();
                //$nin =  $nin.'****';
                $tarif =  $this->getParameter('tarif_formation');
                $salaire = $nbJour * $tarif;
                $montanttotal += $salaire;
                $salaireXOF = number_format($salaire, 0, ',', '.');
                // insert les données
                $sheet->setCellValue('A' . $nextRow, $nextR);
                $sheet->setCellValue('B' . $nextRow, $candid);
                $sheet->setCellValue('C' . $nextRow, $lga);
                $sheet->setCellValue('D' . $nextRow, $departement);
                $sheet->setCellValue('E' . $nextRow, $district);
                $sheet->setCellValue('F' . $nextRow, $numDoss);
                $sheet->setCellValue('G' . $nextRow, $nin);
                $tailleCni =  strlen($nin);

                $sheet->getStyle('G' . $nextRow)->getNumberFormat()->setFormatCode(str_repeat('0', $tailleCni));

                $sheet->setCellValue('H' . $nextRow, $nbJour);
                $sheet->setCellValue('I' . $nextRow, $tarif);
                $sheet->setCellValue('J' . $nextRow, $salaire);
            }
            $montantLettre = $salaireRepository->ConvNumberLetter($montanttotal);
            $nextRow++;
            $sheet->setCellValue('A' . $nextRow, "Montant total")->mergeCells('A' . $nextRow . ':I' . $nextRow);
            $sheet->setCellValue('J' . $nextRow, $montanttotal);
            $spreadsheet->getActiveSheet()->getStyle('A' . $nextRow . ':J' . $nextRow)->applyFromArray(['font' => ['bold' => true]]);
            $nextRow++;
            $sheet->setCellValue('A' . $nextRow, "Arrêté le présent état des frais de transport de " . $nextR . " candidats contractuels à la somme brute de : " . $montantLettre . " (" . $montanttotal . ") Francs CFA.")->mergeCells('A' . $nextRow . ':J' . $nextRow);

            $nextRow++;
            $sheet->setCellValue('A' . $nextRow, "")->mergeCells('A' . $nextRow . ':J' . $nextRow);
            $nextRow++;
            $sheet->setCellValue('A' . $nextRow, "DIRECTEUR GENERAL")->mergeCells('A' . $nextRow . ':J' . $nextRow);
            $spreadsheet->getActiveSheet()->getStyle('A' . $nextRow . ':J' . $nextRow)->applyFromArray(['font' => ['bold' => true, 'size' => 17]]);
            $spreadsheet->getActiveSheet()->getStyle('A' . $nextRow . ':J' . $nextRow)->getAlignment()->setHorizontal('right');
        } catch (\Throwable $th) {
            throw $th;
        }
        return $sheet;
    }

    function resizeFCells($spreadsheet)
    {
        // $sheet->getRowDimension('1')->setRowHeight(-1);
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(25);
    }

    // Télécharger tous les PVs
    #[Route('/pvzip', name: 'app_all_pv_zip', options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function getTicketScreens(PvRepository $repo, KernelInterface $kernel)
    {
        $destZip = sys_get_temp_dir() . "/pvs_rgph5";

        $filesystem = new Filesystem();

        // supprime le dossier créer
        try {
            if (!file_exists(dirname($destZip))) {
                $filesystem = new Filesystem();
                $filesystem->mkdir($destZip, 0777);
            } else {
                $filesystem->remove([$destZip]);

                $filesystem = new Filesystem();
                $filesystem->mkdir($destZip, 0777);
            }
        } catch (\Exception $th) {
            // throw $th;
        }


        try {
            $zip = new \ZipArchive();
            $zip->open($destZip . ".zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $names = $repo->findAll();
            $count = 0;

            foreach ($names as $pv) {

                $theFile = $kernel->getProjectDir() . "/files/uploads/pv/" . $pv->getPv();

                if ($filesystem->exists($theFile)) {
                    // rename file
                    // $extension = pathinfo($pv->getPv(), PATHINFO_EXTENSION);
                    // $newName = $pv->getCommission()->getCustomArrnd() != NULL
                    // ? $pv->getCommission()->getCustomArrnd()->getNom()
                    //     : $pv->getCommission()->getDepartement()->getNom();

                    $zip->addFile($theFile, $pv->getPv());
                    $count++;
                    // rename($theFile, $destZip . "/PV_COM_$newName.$extension");
                    // dump("suis la");
                }
            }

            if ($count == 0) {
                return new Response("<center style='padding-top:250px;'><h1>Oups !!! aucun PV retrouvé</h1></center>");
            }

            $zip->close();
        } catch (\Throwable $th) {
            // throw $th;
        }

        try {
            $filesystem->remove(['symlink', $destZip]);
        } catch (\Throwable $th) {
            //throw $th;
        }

        $response =  new BinaryFileResponse($destZip . ".zip");
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($destZip . ".zip")
        );

        return $response;
    }

    #[Route('/salles-formation/{dept}/{cacr}', name: 'app_salles_formation_rgph5', methods: ['GET'], options: ['expose' => true])]
    public function sallesFormation(SallesRepository $repo, Request $request, Departements $dept, CommunesArrCommunautesRurales $cacr = NULL)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $candidats = $repo->findSalleTraining($dept, $cacr);

        return new JsonResponse(['data' => $candidats]);
    }

    #[Route('/dept_cacrs/{id}', name: 'app_json_dept_cacrs',  methods: ['GET'], options: ['expose' => true])]
    public function deptByCodeRegion(Departements $dept, CommunesArrCommunautesRuralesRepository $repo): Response
    {
        return  new JsonResponse($repo->findDeptCacrs($dept->getCode()));
    }


    // Exportation de la liste des candidats non disponible
    #[Route('/situationsalles', name: 'app_candidats_en_salle_dept', methods: ['GET'], options: ['expose' => true])]
    public function situationCandidatsEnSalle(
        DepartementsRepository $repo,
        CommunesArrCommunautesRuralesRepository $cacrRepo
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $dt = new DateTime();
        $df = $dt->format('d_m_Y_H_i');

        $departements = $repo->findBy([], ['code' => 'ASC']);

        $fileName = "SITUATION_AFFECTATION_AR_DEPT_$df.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetClassroomStat($departements, $sheet, $cacrRepo);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheetClassroomStat($departements, $sheet, $cacrRepo)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "DEPARTEMENT;COD_DEPT;TOTAL_LISTE_PRINCIPALE;NBR_EN_SALLE_LP;NBR_AR_PAS_DE_SALLE_LP;TOTAL_LISTE_ATTENTE;NBR_EN_SALLE_LA;NBR_AR_PAS_DE_SALLE_LA";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($departements as $dept) {
            $deptName = $dept->getNom();
            $deptCod = $dept->getCode();

            // Liste Principale
            $totalLP = 0;
            $totalLPAffected = 0;
            $totalLPANotffected = 0;

            // Liste Attente
            $totalLA = 0;
            $totalLAAffected = 0;
            $totalLAANotffected = 0;

            $deptCacrs = $cacrRepo->findDeptCacrs($deptCod);
            foreach ($deptCacrs as $cacr) {
                // liste principale
                $totalLP += $cacr->getTotalSelection();
                $totalLPAffected += $cacr->getTotalSelectionEnSalle();
                $totalLPANotffected += $cacr->getTotalSelectionPasEnSalle();
                // Liste Attente
                $totalLA += $cacr->getTotalReserviste();
                $totalLAAffected += $cacr->getTotalReservisteEnSalle();
                $totalLAANotffected += $cacr->getTotalReservistePasEnSalle();
            }

            $myVariableCSV = "$deptName|$deptCod|$totalLP|$totalLPAffected|$totalLPANotffected|$totalLA|$totalLAAffected|$totalLAANotffected";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }
        return $sheet;
    }

    // Exportation de la liste des candidats non disponible
    #[Route('/superviseur/{id}/salle-excel', name: 'app_download_superviseur_salle_excel', methods: ['GET'], options: ['expose' => true])]
    public function exportSalle(
        User $superviseur,
        SallesRepository $repo
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $deptArrnd = $superviseur->getCustomArrnd() != NULL
            ? $superviseur->getCustomArrnd()->getNom()
            : $superviseur->getDepartement()->getNom();

        $myLogin = $superviseur->getEmail();

        $candidats = $repo->findBy(['superviseur' => $superviseur], ['login' => 'ASC']);

        $fileName = "SALLE_FORMATION_$myLogin.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetSupervisorClassroom($candidats, $sheet, $deptArrnd);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheetSupervisorClassroom($candidats, $sheet, $deptArrnd)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "DEPARTEMENT;COD_DEPT;NUMERO_DOSSIER;PRENOM;NOM;DATE_NAISSANCE;LOGIN;PASSWORD;SALLE_FORMATION";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [1, 2, 6, 7])) {
                $sheet->getColumnDimension($col)->setWidth(17);
            } else if ($key == 8) {
                $sheet->getColumnDimension($col)->setWidth(50);
            } else {
                $sheet->getColumnDimension($col)->setWidth(30);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($candidats as $candidat) {
            // $deptName = $candidat->getCandidat()->getDepartement()->getNom();
            $codDept = $candidat->getCandidat()->getDepartement()->getCode();
            $numeroDossier = $candidat->getCandidat()->getNumeroDossier();
            $name = $candidat->getCandidat()->getNom();
            $prenom = $candidat->getCandidat()->getPrenom();
            $login = $candidat->getLogin();
            $password = $candidat->getPassword();
            $maSalle = $candidat->getSuperviseur()->getAdresse();

            $dateNaiss = $candidat->getCandidat()->getDateNaissance();
            $dnaissFormat = $dateNaiss->format('d/M/Y');

            $myVariableCSV = "$deptArrnd|$codDept|$numeroDossier|$prenom|$name|$dnaissFormat|$login|$password|$maSalle";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1 || $x == 2 || $x == 6) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }
        return $sheet;
    }


    // TODO: TEMPLATE COPATEG
    #[Route('/copatage/{id}/template', name: 'app_export_coptage_template', methods: ['GET'], options: ['expose' => true])]
    public function cdtCopatge(Departements $dept, CommunesArrCommunautesRuralesRepository $repo, UserRepository $usersRepo): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $connected = $usersRepo->findOneBy(['id' => $this->getUser()]);

        $commissions = "";
        $sheetName = "";

        if ($connected->getCustomArrnd() == null) {
            $sheetName = "_DEPT_" . $dept->getNom() . "_" . $dept->getCode();

            $myCacrs = $repo->findDeptCacrs($dept->getCode());
            foreach ($myCacrs as $cacr) {
                $commissions .= $cacr->getNom() . ",";
            }
        } else {
            $sheetName = "_ARRND_" . $connected->getCustomArrnd()->getNom() . "_" . $dept->getCode();
            foreach ($connected->getCustomArrnd()->getCustomArrondissementCommunes() as $custArrndCacr) {
                $commissions .= $custArrndCacr->getCacr()->getNom() . ",";
            }
        }

        $commissions = strlen($commissions) > 0 ? substr($commissions, 0, strlen($commissions) - 1) : "";

        $fileName = "TEMPLATE_COPTAGE" . $sheetName . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetCopatgeTemplate($sheet, $commissions);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheetCopatgeTemplate($sheet, $comNames)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "COMMUNE_TRAVAIL;PRENOM;NOM;SEXE;DATE DE NAISSANCE;LIEU DE NAISSANCE;SITUATION MATRIMONIALE;ADRESSE;EMAIL;CNI;DATE DELIV. CNI;DERNIER DIPLÔME;PROFESSION;TELEPHONE_IDENTIFIE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [3])) {
                $sheet->getColumnDimension($col)->setWidth(8);
            } else {
                $sheet->getColumnDimension($col)->setWidth(28);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        /**
         * Set cell B3 with the "Select from the drop down options:"
         * string value, will serve as the 'Select Option Title'.
         */
        // $sheet->setCellValue('B3', 'Select from the drop down options:');
        $this->setCellDropDown($sheet, 'A', '"' . $comNames . '"', "Chosissez sa district de travail sur la liste déroulante");
        $this->setCellDropDown($sheet, 'D', '"Homme, Femme"', 'Sélectionnez le sexe sur la liste déroulante');
        $this->setCellDropDown($sheet, 'G', '"Marié, Célibataire, Divorcé, Veuf/ve, Séparé, Union Libre"', 'Situation matrimoniale');
        $this->setCellDropDown($sheet, 'L', '"Niveau 4ème, BFEM, BAC, BAC+2"', 'Sélectionnez son dernier diplôme obtenu');

        return $sheet;
    }

    // Exportation de la liste des candidats non disponible
    #[Route('/superviseur/{id}/reequilibrage-sheet', name: 'app_superviseur_excel_reequilibrage_sheet', methods: ['GET'], options: ['expose' => true])]
    public function rebalancingExcel(
        User $superviseur,
        CompositionRepository $repo
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $deptArrnd = $superviseur->getCustomArrnd() != NULL
            ? $superviseur->getCustomArrnd()->getNom()
            : $superviseur->getDepartement()->getNom();

        $myLogin = $superviseur->getEmail();

        $teams = $repo->findSupCompositon($superviseur);

        $fileName = "EQUIPE_" . $deptArrnd . "_" . $myLogin . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetReequilibrageProtorype($teams, $sheet, $deptArrnd, $spreadsheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    /**
     * Undocumented function
     *
     * @param Composition $candidats[]
     * @param [type] $sheet
     * @param string $deptArrnd
     * @return void
     */
    public function sheetReequilibrageProtorype($candidats, $sheet, $deptArrnd, $spreadsheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "DEPARTEMENT/ARRND;COMMUNE;PROFIL;PRENOM;NOM;ADRESSE;NUMERO_DOSSIER;LOGIN;PASSWORD;TELEPHONE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [6, 7, 8])) {
                $sheet->getColumnDimension($col)->setWidth(19);
            } else {
                $sheet->getColumnDimension($col)->setWidth(26);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        $i = 2;
        foreach ($candidats as $candidat) {
            $numeroDossier = $candidat->getNumDossier();
            $name = $candidat->getArr()->getNom();
            $prenom = $candidat->getArr()->getPrenom();
            $login = $candidat->getArr()->getEmail();
            $password = $candidat->getArr()->getPasswordView();
            $adress = $candidat->getAdress();
            $cacr = $candidat->getArr()->getCommune()->getNom();
            $phone = $candidat->getArr()->getTelephone();

            $profil = $candidat->getArr()->getRoles()[0];
            $myRole = "";
            if ($profil == "ROLE_CE") {
                $myRole = "Contrôleur";
                $this->cellColor($spreadsheet, $i, "CE");
            } else if ($profil == "ROLE_AR") {
                $myRole = "AR Classique";
            }

            if (in_array($profil, ["ROLE_CE", "ROLE_AR"])) {
                $myVariableCSV = "$deptArrnd|$cacr|$myRole|$prenom|$name|$adress|$numeroDossier|$login|$password|$phone";
                $mesValeurs = explode('|', $myVariableCSV);
                for ($x = 0; $x < count($mesValeurs); $x++) {
                    if ($x == 6 || $x == 7 || $x == 8) {
                        $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                    }
                }

                $i++;
            }
        }
        return $sheet;
    }

    function cellColor($spreadsheet, $pos, $profil)
    {
        if ($profil == "AR") {
            $spreadsheet->getActiveSheet()->getStyle('A' . $pos . ':J' . $pos)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => '808000'
                    ]
                ],
            ]);
        } else if ($profil == "CE") {
            $spreadsheet->getActiveSheet()->getStyle('A' . $pos . ':J' . $pos)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFDBE2F1',
                    ]
                ],
            ]);
        } else if ($profil == "RAT" || $profil == "PCP") {
            $spreadsheet->getActiveSheet()->getStyle('A' . $pos . ':J' . $pos)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFDBE2F1',
                    ]
                ],
            ]);
        }
    }

    // TODO URGENCE Docteur MANE
    #[Route('/exportCompo-sheet', name: 'app_template_compo_profilage', methods: ['GET'], options: ['expose' => true])]
    public function sheetCompo(CandidaturesAticRepository $repo, Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);


        $dt = new \DateTime();
        $fileName = "TEMPLATE_COMPO_EQUIPE.xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->compoBase($sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function compoBase($sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "LOGIN_SUPERVISEUR;NUMERO_DOSSIER;USER_TYPE";
        $colonnesExcel = ['A', 'B', 'C',];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)

        $this->setCellDropDown($sheet, 'C', '"0, 3, 4, 5"', "Profil de l'agent");

        return $sheet;
    }

    //  Publi postage contrat remplacant
    // TODO URGENCE Docteur MANE
    #[Route('/export-remplacant', name: 'app_remplacants_export_sheet', methods: ['GET'], options: ['expose' => true])]
    public function exportSheetRemplacant(RemplacementArCollecteRepository $repo, UserRepository $userRepository)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $connectedUser = $userRepository->findOneBy(['id' => $this->getUser()]);

        $arrndCacrs = [];
        if ($connectedUser->getCustomArrnd()) {
            $homeWorks = $connectedUser->getCustomArrnd()->getCustomArrondissementCommunes();
            $arrndCacrs =  array_map(function ($cacr) {
                return $cacr->getCacr()->getCode();
            }, $homeWorks->toArray());
        }

        $dt = new \DateTime();
        $df = $dt->format('d_m_Y_H_i');
        $fileName = "LISTE_RESERVISTE_$df.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $users = $repo->findRemplacants($connectedUser->getDepartement(), $arrndCacrs);

        $this->sheetRemplacantCollecte($sheet, $users);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    /**
     * Undocumented function
     *
     * @param [type] $sheet
     * @param [Users] $users[]
     * @return void
     */
    public function sheetRemplacantCollecte($sheet, $users)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "LOGIN_COLLECTE;PRENOM;NOM;SEXE;CNI;DATE_NAISS;LIEU_NAISS;TELEPHONE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)

        //  $this->setCellDropDown($sheet, 'C', '"0, 3, 4, 5"', "Profil de l'agent");

        $i = 2;
        foreach ($users as $rem) {

            $remplacant = $rem->getRemplacant();

            $login = $remplacant->getEmail();
            $name = $remplacant->getNom();
            $prenom = $remplacant->getPrenom();
            $login = $remplacant->getEmail();
            $adress = $remplacant->getAdresse();
            // $cacr = $remplacant->getCommune()->getNom();
            $phone = $remplacant->getTelephone();
            $nin = $remplacant->getCni();

            $sexe = substr($nin, 0, 1) == 1 ? 'Homme' : 'Femme';

            $datenaiss = $remplacant->getDatenaiss() ? $remplacant->getDatenaiss()->format('d/m/Y') : "";
            $lieunaiss = $remplacant->getLieuNaiss();

            $myVariableCSV = "$login|$prenom|$name|$sexe|$nin|$datenaiss|$lieunaiss|$phone";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 0 || $x == 4) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }

        return $sheet;
    }

    // TODO: TEMPLATE COPATEG
    #[Route('/all_users', name: 'app_all_users', methods: ['GET'], options: ['expose' => true])]
    public function checkInfo(CompositionRepository $compoRepo, CandidaturesRepository $candidatureRepo, UserRepository $usersRepo): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $connected = $usersRepo->findOneBy(['id' => $this->getUser()]);


        $fileName = "ALL_USERS.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $agentRecenseurs = $compoRepo->findBy([], ['numDossier' => 'ASC']);
        // $superviseurs = $repo->findSuperviseurs();

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetAllUsers($sheet, $agentRecenseurs, $candidatureRepo);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheetAllUsers($sheet, $agentRecenseurs, $candidatureRepo)
    {
        $profils = [
            "ROLE_CE" => "3",
            "ROLE_AR" => "4",
            "ROLE_AR_RATISSAGE" => "5",
            "ROLE_AR_PCP" => "7"
        ];
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "ETAT_ID_DOSSIER;LOGIN_FROM_CANDIDAT;FIELD_NUM_FOLDER;FIELD_LOGIN;ETAT_PRENOM;ETAT_NOM;ETAT_NIN;ETAT_MODE_PAIEMENT;ETAT_TELEPHONE;STATUT_MAJ;AGENT_UUID;PROFIL";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [0, 1, 2])) {
                $sheet->getColumnDimension($col)->setWidth(15);
            } else {
                $sheet->getColumnDimension($col)->setWidth(28);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        $i = 2;
        foreach ($agentRecenseurs as $compo) {
            $login = $compo->getArr()->getEmail();
            $numeroDossier = $compo->getNumDossier();
            $prenom = $compo->getArr()->getPrenom();
            $name = $compo->getArr()->getNom();
            $nin = $compo->getArr()->getCni();
            $modePaid = $compo->getModePaiement();
            $moyenPaiement = !empty($compo->getModePaiement()) && $compo->getModePaiement() == "non défini" ? "" : $compo->getModePaiement();
            $phone = $compo->getArr()->getTelephone();

            $role = $compo->getArr()->getRoles()[0];

            $profil = $profils[$role];

            if (empty($numeroDossier)) {
                $cand = $candidatureRepo->findOneBy(['nin' => $compo->getArr()->getCni()]);
                $numeroDossier = $cand != NULL ? $cand->getNumeroDossier() : "";
            }

            $uuid36 = $compo->getArr()->getUuid();

            $modPaidToNumber = "";
            if (!empty($compo->getModePaiement()) && strtoupper($compo->getModePaiement()) == "ORANGE MONEY") {
                $modPaidToNumber = 1;
            } else if (!empty($compo->getModePaiement()) && strtoupper($compo->getModePaiement()) == "WAVE") {
                $modPaidToNumber = 2;
            } else if (!empty($compo->getModePaiement()) && strtoupper($compo->getModePaiement()) == "FREE MONEY") {
                $modPaidToNumber = 3;
            } else {
                $modPaidToNumber = 1;
            }

            $myVariableCSV = "$numeroDossier|$login|$numeroDossier|$login|$prenom|$name|$nin|$modPaidToNumber|$phone|0|$uuid36|$profil";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 0 || $x == 1 || $x == 2 || $x == 3 || $x == 6) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }


        $this->setCellDropDown($sheet, 'L', '"3, 4, 5, 7"', 'Profil');

        return $sheet;
    }


    // TODO: Export Pré-etat paiement SRH
    // TODO: TEMPLATE COPATEG
    #[Route('/pre-etat/{id}/dept', name: 'app_export_pre_etat_paid_departement', methods: ['GET'], options: ['expose' => true])]
    public function exportPreEtatPaid(Departements $departement, MapUserPaidRepository $repo): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $dt = new \DateTime();
        $df = $dt->format('d_m_Y_H_i');

        $deptNames = $departement->getNom() . " " . $departement->getCode() . "_";

        $fileName = "PRE_ETAT_PAIEMENT_DEPT_$deptNames" . "$df.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $agentsRecenseurs = $repo->findEligibleForPaid($departement, "0");
        // $superviseurs = $repo->findSuperviseurs();

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetPreEtatPaid($sheet, $agentsRecenseurs);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    /**
     * Sheet template for eligible agent
     *
     * @param [type] $sheet
     * @param MapUserPaid[] $agentsRecenseurs
     * @return void
     */
    public function sheetPreEtatPaid($sheet, $agentsRecenseurs)
    {
        $montantBrut = [
            3 => 184211, // Contrôleur
            4 => 157895, // AR classique
            5 => 157895, // AR ratissage
            7 => 157895 // AR PCP
        ];
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "REGION;DEPARTEMENT;COD_DR2022;COMMUNE;PROFIL;NUM_DOSSIER;PRENOM;NOM;NIN;TELEPHONE;MODE_PAIEMENT;LOGIN_SUPERVISEUR; MONTANT BRUT";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            if (in_array($key, [2, 5])) {
                $sheet->getColumnDimension($col)->setWidth(16);
            } else {
                $sheet->getColumnDimension($col)->setWidth(20);
            }
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        $i = 2;
        foreach ($agentsRecenseurs as $paid) {
            $lga = $paid->getDepartement()->getRegion()->getNom();
            $dept = $paid->getDepartement()->getNom();
            $codDr2022 = $paid->getCoddr2022() != NULL ? $paid->getCoddr2022() : "";
            $district = $paid->getCommuneDr() != NULL ? $paid->getCommuneDr() : "";

            $profil = $paid->getRoleName();
            $dossier = $paid->getNumDossier();
            $prenom = $paid->getPrenom();
            $name = $paid->getNom();
            $nin = $paid->getNin();
            $phone = $paid->getTelephone();
            $modePaid = $paid->getModePaidName();
            $loginSuperviseur = $paid->getLoginSup();
            $montant = $montantBrut[$paid->getRoles()];

            $myVariableCSV = "$lga|$dept|$codDr2022|$district|$profil|$dossier|$prenom|$name|$nin|$phone|$modePaid|$loginSuperviseur|$montant";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 2 || $x == 5 || $x == 8) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }

        if (count($agentsRecenseurs) > 0) {
            $this->setCellDropDown($sheet, 'E', '"Contrôleur, AR classique, AR ratissage, AR PCP"', 'Profil');
            $this->setCellDropDown($sheet, 'K', '"Orange Money, Wave, Free Money"', 'Mode de paiement souhaité');
        }

        return $sheet;
    }

    // TODO: Export Pré-etat paiement SRH
    // TODO: TEMPLATE COPATEG
    #[Route('/pre-etat/exportAll', name: 'app_export_pre_etat_all_paid', methods: ['GET'], options: ['expose' => true])]
    public function exportAllPaidUsers(MapUserPaidRepository $repo, UserRepository $usersRepo): Response
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $me = $usersRepo->findOneBy(['id' => $this->getUser()]);

        $codRegion = NULL;
        $outputName = "_ALL";


        if (!$this->isGranted("ROLE_ADMIN") and $this->isGranted("ROLE_SRSD")) {
            $codRegion = $me->getDepartement()->getRegion()->getCode();
            $outputName = "_REG_" . $me->getDepartement()->getRegion()->getNom();
        }

        $dt = new \DateTime();
        $df = $dt->format('d_m_Y_H_i');

        $fileName = "PRE_ETAT_PAIEMENT_DEPT_$outputName" . "$df.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $agentsRecenseurs = $repo->extractHQ($codRegion, "0");
        // $superviseurs = $repo->findSuperviseurs();

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheetPreEtatPaid($sheet, $agentsRecenseurs);

        $writer->save($fileName);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    // TODO: EPC
    // Exporter le template de base de la création des équipes
    #[Route('/epc-export-team-template', name: 'app_epc_export_team_template', methods: ['GET'], options: ['expose' => true])]
    public function epcExportTeamTemplate(Request $request, UserRepository $repo)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);


        $fileName = "EPC_TEMPLATE_CREATION_EQUIPE.xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $userNamesWithComma = "";
        $userNames = "";

        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $superviseursEpc = $repo->findUserByRoles("ROLE_SUPERVISEUR_EPC");
            foreach ($superviseursEpc as $us) {
                $userNamesWithComma .= $us->getEmail() . ",";
            }

            $userNames = strlen($userNamesWithComma) > 0 ? substr($userNamesWithComma, 0, strlen($userNamesWithComma) - 1) : "";
        }

        $this->epcTeamSheet($sheet, $userNames);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });

        return $response;
    }

    public function epcTeamSheet($sheet, $supLogins = "")
    {
        $myVariableCSV = "";
        $colonnesExcel = [];

        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $myVariableCSV = "LOGIN_SUPERVISEUR;PROFIL;PRENOM;NOM;SEXE;TELEPHONE;CNI";
            $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        } else {
            $myVariableCSV = "PROFIL;PRENOM;NOM;SEXE;TELEPHONE;CNI";
            $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F'];
        }

        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            $sheet->getColumnDimension($col)->setWidth(25);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)

        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $this->setCellDropDown($sheet, 'A', '"' . $supLogins . '"', "Chosissez superviseur EPC");
            $this->setCellDropDown($sheet, 'B', '"Chef d\'équipe, Agent enquêteur, Chauffeur"', "Profil de l'agent");
            $this->setCellDropDown($sheet, 'E', '"Homme, Femme"', "Choisir le sexe");
        } else {
            $this->setCellDropDown($sheet, 'A', '"Chef d\'équipe, Agent enquêteur, Chauffeur"', "Profil de l'agent");
            $this->setCellDropDown($sheet, 'D', '"Homme, Femme"', "Choisir le sexe");
        }

        return $sheet;
    }

    #[Route('/epc-export-dispatching-template', name: 'app_epc_export_dispatching_template', methods: ['GET'], options: ['expose' => true])]
    public function epcDispatchingTemplate(UserRepository $repo, EpcAgentsRepository $epcAgentsRepo, DrEpcRepository $drEpcRepository)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $connected = $repo->findOneBy(["id" => $this->getUser()]);

        $myTeams = $epcAgentsRepo->findMyTeamsEPC($connected->getEmail());

        // $myDrsEpc = $connected->getDrEpcs();
        $myDrsEpc = $drEpcRepository->findUnaffectedDrs($connected);

        $userNames = "";
        $myDrs = "";

        foreach ($myTeams as $eq) {
            $userNames .= $eq->getUserName() . ",";
        }

        // foreach ($myDrsEpc as $dr) {
        //     $myDrs .= $dr->getCodDr2022() . ",";
            // $myDrs .= $dr->getCodDr2022() . "|" . $dr->getCompDr2022() . ",";
        // }

        $userNamesComma = strlen($userNames) > 0 ? substr($userNames, 0, strlen($userNames) - 1) : "";
        $myDrsComma = strlen($myDrs) > 0 ? substr($myDrs, 0, strlen($myDrs) - 1) : "";

        // dump($userNamesComma);
        // dd($myDrsComma);

        $fileName = "EPC_TEMPLATE_DISPATCHING_DR_EQUIPE.xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->epcDispatchingDrSheet($sheet, $userNamesComma, $myDrsEpc);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });

        return $response;
    }

    public function epcDispatchingDrSheet($sheet, $userNames, $drs)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "LOGIN_CONTROLEUR;DR_DE_TRAVAIL";
        $colonnesExcel = ['A', 'B',];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            $sheet->getColumnDimension($col)->setWidth(25);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }


        $i = 2;
        foreach ($drs as $dr) {
            $codDr2022 = $dr->getCodDr2022();
            $drName2022 = $dr->getCompDr2022();

            $myVariableCSV = "|$codDr2022";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                    // $xx = $colonnesExcel[$x] . $i;
                    // $this->setCellDropDown($sheet, $xx, '"' .  $codDr2022 . '"', "DR $codDr2022 | $drName2022");
                }
            }

            $i++;
        }

        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)

        $this->setCellDropDown($sheet, 'A', '"' . $userNames . '"', "Chosissez une d'équipe");

        return $sheet;
    }

    #[Route('/epc-export-superviseurs-template', name: 'app_epc_export_superviseurs_template', methods: ['GET'], options: ['expose' => true])]
    public function epcTemplateSuperviseurs()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $fileName = "EPC_TEMPLATE_SUPERVISEURS.xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->epcSuperviseursSheet($sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });

        return $response;
    }

    public function epcSuperviseursSheet($sheet)
    {
        $myVariableCSV = "PRENOM;NOM;SEXE;TELEPHONE;CNI";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            $sheet->getColumnDimension($col)->setWidth(25);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        $this->setCellDropDown($sheet, 'C', '"Homme, Femme"', "Choisir le sexe");

        return $sheet;
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\Utils;
use App\Repository\UserRepository;
use App\Repository\ClassroomRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Finder\Finder;
use ZipArchive;

#[Route('/export')]
class CensusExportController extends AbstractController
{

    // Export Data
    #[Route('/sp-salle/{slug}', name: 'export_sp_salle_candidats', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function spClassroom(User $sp, ClassroomRepository $repo)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $spLogin = $sp->getUsername();
        $dt = new \DateTime();
        $df = $dt->format('d_m_Y_H_i_s');
        $fileName = "Enumerators_$spLogin" . "_$df.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $candidats = $repo->findBy(["supervisor" => $sp]);
        $this->spClassroomCanava($sheet, $candidats);

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

    public function spClassroomCanava($sheet, array $candidats)
    {

        $myVariableCSV = "LOGIN_SUPERVISOR;IS_PROFILE;LOGIN_ENUMERATOR;PRENOMS;NOM;SEXE;DATE_NAISS;NOMBRE_JR_PRESENCE;NOTE_FORMATION";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $i = 1;
        foreach ($colonnesExcel as $key => $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }

        $i = 2;
        foreach ($candidats as $sCand) {
            $spUserName = "SP" . substr($sCand->getUsername(), 0, 4);
            $numDepot = $sCand->getUsername();
            $prenoms = $sCand->getEnumerator()->getName();
            $nom = $sCand->getEnumerator()->getSurname();
            $sexe = $sCand->getEnumerator()->getSex();
            $dateNaiss = $sCand->getEnumerator()->getBirthDate()->format('d/m/Y');
            $nbPresence = $sCand->getTotalPresence();
            $note = !empty($sCand->getNote()) ? $sCand->getNote() : 0;

            $isProfile = ($sCand->isIsProfile() != null && $sCand->isIsProfile()) ? 'Yes' : 'No';

            $myVariableCSV = "$spUserName|$isProfile|$numDepot|$prenoms|$nom|$sexe|$dateNaiss|$nbPresence|$note";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
            }

            $i++;
        }

        $this->setCellDropDown($sheet, 'B', '"Yes, No"', 'Est-il retenu après la formation ?');

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

    // Download all pieces comptables
    #[Route('/pieces-compta', name: 'export_all_pieces_compta', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function piecesComptas(
        UserRepository $repo,
        ClassroomRepository $classroomRepository,
        KernelInterface $kernel,
    ) {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);


        $destination = $kernel->getProjectDir() . '/var/gbos';

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

        $supervisors = $repo->findUserByRoles('ROLE_SUPERVISOR');

        $dt = new \DateTime();
        $basename = '/GBOS_PIECES_COMPTA_' . $dt->format('d_m_Y_H_i') . '_' . uniqid() . '.zip';

        foreach ($supervisors as $sp) {

            $candidats = $classroomRepository->findBy(["supervisor" => $sp]);

            $fileName = $sp->getUsername() . "_ENUMERATORS.xlsx";
            $spreadsheet = new Spreadsheet();
            $writer = new Xlsx($spreadsheet);

            $sheet = $spreadsheet->getActiveSheet();

            $sheets = $this->spClassroomCanava($sheet, $candidats);

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


        $destZip = sys_get_temp_dir() . $basename;

        // récupère les feuilles d'émargement
        try {
            $this->cloneEmargementFiles($this->getParameter('presentielBasePath'), $destination . "/payroll_sheets", $destZip);
            $zipperPath = $destination . "/payroll_sheets.zip";
            $this->zipFolder($destination . "/payroll_sheets", $zipperPath);
            array_push($filesNames, "payroll_sheets.zip");
        } catch (\Throwable $th) {
            //throw $th;
        }

        $allPiecesJointes =  array_map(function ($p) use ($destination) {
            return $destination . '/' . $p;
        }, $filesNames);

        Utils::createZipArchive($allPiecesJointes, $destZip);

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

    public function cloneEmargementFiles($sourcePath, $destinationPath, $destZip)
    {
        $filesNames = array();

        // Create a Finder instance
        $finder = new Finder();

        // Find all files and directories in the source path
        $finder->in($sourcePath);

        // Create a Filesystem instance
        $filesystem = new Filesystem();

        // Loop through the Finder results and copy each file/directory
        foreach ($finder as $file) {
            $sourceFile = $file->getRealPath();
            $destinationFile = $destinationPath . '/' . $file->getRelativePathname();

            // Check if it's a directory and create it in the destination if needed
            if ($file->isDir()) {
                $filesystem->mkdir($destinationFile);
            } else {
                // Copy the file
                $filesystem->copy($sourceFile, $destinationFile, true);
            }
        }
    }

    public function zipFolder($folderPath, $zipFilePath)
    {
        // use Symfony\Component\Finder\Finder;

        // Specify the path to the existing folder
        // $folderPath = '/path/to/existing/folder';

        // Specify the path for the new zip file
        // $zipFilePath = '/path/to/new/file.zip';

        // Create a ZipArchive instance
        $zip = new \ZipArchive();

        // Open the zip file for writing
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            die("Cannot open {$zipFilePath} for writing");
        }

        // Use Symfony Finder to get all files in the folder
        $finder = new Finder();
        $finder->files()->in($folderPath);

        // Iterate through each file and add it to the zip
        foreach ($finder as $file) {
            // Add the file to the zip with its relative path
            $zip->addFile($file->getRealPath(), $file->getRelativePathname());
        }

        // Close the zip file
        $zip->close();

        // echo "Folder successfully zipped to {$zipFilePath}";
    }
}

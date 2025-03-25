<?php

namespace App\Utils;

use App\Entity\Departements;
use App\Entity\User;
use App\Repository\SallesRepository;
use App\Repository\UserRepository;
use Symfony\Component\Filesystem\Filesystem;

class Utils
{

    public static function scpCopy($fileSrcCpy, $fileDestName): string
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $filesystem = new Filesystem();
        $outputStr = "";

        // Permet de tester l'existence du fichier csdb à copier vers le serveur distant
        $isCsdbExists = $filesystem->exists([$fileSrcCpy]);

        if ($isCsdbExists) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                //echo 'This is a server using Windows!';
                // $cmd = str_replace('/', '\\', $fileSrcCpy);
                // $command = "xcopy $cmd E:\\backups\\$fileDestName\\";
                // exec($command, $output, $retval);
                // $outputStr = $output[0];
            } else {
                //echo 'This is a server not using Windows!';
                $password = "PigD@t@Ba$" . "e2022";
                $command = "sshpass -p '$password' scp $fileSrcCpy censusmp-db@10.7.0.12:/var/www/html/csweb_denombrement/files/$fileDestName/";
                $outputStr = shell_exec($command);

                //$rsyncCmd = "rsync $fileSrcCpy censusmp-db@10.7.0.150:/var/www/html/csweb_denombrement/files/$fileDestName/";
                //$outputStr = shell_exec($rsyncCmd);
            }
        } else {
            $outputStr = "$fileSrcCpy n'esiste pas !";
        }

        return $outputStr != null ? $outputStr : "";
    }


    public static function deleteExcelTmpFile($tmpFile, $fileFolder)
    {
        $filesystem = new Filesystem();
        $filesystem->remove(['symlink', $fileFolder . $tmpFile]);
    }

    public static function createZipArchive($files = array(), $destination = '', $overwrite = false)
    {
        if (file_exists($destination) && !$overwrite) {
            return false;
        }

        $validFiles = array();
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $validFiles[] = $file;
                }
            }
        }

        if (count($validFiles)) {
            $zip = new \ZipArchive();
            if ($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE)) {
                foreach ($validFiles as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();
                return file_exists($destination);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

  
    public static function getContentDirectoryWithStartEndValue($scanDir, $startWith, $endWith): array
    {
        // $files = preg_grep('~^tpl-.*\.php$~', scandir(admin . "templates/default/"));
        $files = preg_grep('~^' . $startWith . $endWith . '$~', scandir($scanDir));
        // $files = preg_grep('~^' . $startWith . '.*\.' . $endWith . '$~', scandir($scanDir));
        return $files;
    }

    public static function generateUniqCod(UserRepository $userRepository, $len = 10)
    {
        $word = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        shuffle($word);
        $code = substr(implode($word), 0, $len);

        $isUsed = $userRepository->isDuplicated($code);

        if ($isUsed) {
            self::generateUniqCod($userRepository, 6);
        }

        return $code;
    }

    public static function numberDaysToExpire($expireDate): int
    {
        $now = time(); // or your date as well
        $your_date = strtotime($expireDate);
        // $your_date = strtotime("2010-01-31");
        $datediff = $now - $your_date;

        return round($datediff / (60 * 60 * 24));
    }


    public static function isGoodNumber($telephone): bool
    {
        if (!is_integer(intval($telephone))) {
            return false;
        }

        $startWith = substr($telephone, 0, 2);

        $isRightOperator = in_array($startWith, ['77', '78', '76', '75', '70']);
        $isLength = strlen($telephone) == 9 ? true : false;

        if ($isRightOperator && $isLength) {
            return true;
        }

        return false;
    }

    public static function findLetter($login): string
    {
        if (str_contains($login, 'A')) {
            return 'A';
        } else  if (str_contains($login, 'B')) {
            return 'B';
        } else  if (str_contains($login, 'C')) {
            return 'C';
        } else  if (str_contains($login, 'D')) {
            return 'D';
        } else  if (str_contains($login, 'E')) {
            return 'E';
        } else  if (str_contains($login, 'F')) {
            return 'F';
        } else  if (str_contains($login, 'G')) {
            return 'G';
        } else  if (str_contains($login, 'H')) {
            return 'H';
        } else  if (str_contains($login, 'I')) {
            return 'I';
        } else  if (str_contains($login, 'J')) {
            return 'J';
        } else  if (str_contains($login, 'K')) {
            return 'J';
        }

        return "";
    }

    public static function rotatePicture($srcPath, $login)
    {
        try {
            $rotateFilename = $srcPath; // PATH
            $degrees = -90;

            $fileType = strtolower(substr("photo_$login.jpg", strrpos("photo_$login.jpg", '.') + 1));

            if ($fileType == 'png') {
                header('Content-type: image/png');
                $source = imagecreatefrompng($rotateFilename);
                $bgColor = imagecolorallocatealpha($source, 255, 255, 255, 127);
                // Rotate
                $rotate = imagerotate($source, $degrees, $bgColor);
                imagesavealpha($rotate, true);
                imagepng($rotate, $rotateFilename);
            }

            if ($fileType == 'jpg' || $fileType == 'jpeg') {
                header('Content-type: image/jpeg');
                $source = imagecreatefromjpeg($rotateFilename);
                // Rotate
                $rotate = imagerotate($source, $degrees, 0);
                imagejpeg($rotate, $rotateFilename);
            }

            // Free the memory
            imagedestroy($source);
            imagedestroy($rotate);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public static function numberDaysWork($startAt, $endDate): int
    {
        // $startDate = strtotime($startAt);
        // $endDate = strtotime($endDate);
        // $datediff = $endDate - $startDate;

        // return round($datediff / (60 * 60 * 24));

        $startDate = new \DateTime($startAt);
        $endDate = new \DateTime($endDate);

        $abs_diff = $endDate->diff($startDate)->format("%a"); //3

        return $abs_diff;
    }

    /**
     * Génère un Uuid unique 
     *
     * @param integer $length nombre de digits à retourner
     * @return string
     */
    public static function str_rand(int $length = 54): string
    { // 64 = 32
        $length = ($length < 4) ? 4 : $length;
        return bin2hex(random_bytes(($length - ($length % 2)) / 2));
    }

    public static function generateCaseID(): string
    {
        return Utils::str_rand(8) . "-" . Utils::str_rand(4) . "-" . Utils::str_rand(4) . "-" . Utils::str_rand(4) . "-" . Utils::str_rand(12);
    }

    public static function mapExcelColumns($spreadsheet): array
    {
        $colonnes = array();

        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        foreach ($sheetData as $Row) {
            array_push($colonnes, $Row);
            break;
        }
        return $colonnes[0];
    }

    public static function excelNotFoundColumnException($colums, $mapColumns): string
    {
        foreach ($colums as $col) {
            $isColumn = array_search($col, $mapColumns, true);

            if ($isColumn == false) {
                return $col;
            }
        }
        return "";
    }
}

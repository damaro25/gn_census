<?php

declare(strict_types=1);

namespace App\PDF;

use Symfony\Component\HttpKernel\KernelInterface;
use TCPDF;

class CensusPdf extends TCPDF
{
    private static $X = 10;
    private static $Y = 90;
    private $commission = "";

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


        $this->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
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
        $this->SetFont('helvetica', '', 9);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $this->AddPage();

        // $this->SetAutoPageBreak(TRUE, 0);

        // set text shadow effect
        $this->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // gambia
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/gambia.png', 70, 2, 70, 30, 'PNG');

        // set logo GBOS
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_grille.png', 10, 30, 40, 30, 'PNG');
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png', 10, 30, 40, 25, 'PNG', '', 'T', false, 300, 'R', false, false, 0,     false, false, false);
        $this->setX(10);
        $this->setY(48);
        $this->write(20, "Je suis recensé (e), je compte !", '', false, "R");


        $this->setX(10);
        $this->setY(70);

        $this->writeHTML("<h3>$title</h3>", true, false, false, false, 'C');

        // $this->setCellPaddings(0, , 0, 0);
        //$this->SetMargins(10, 55, 10, true);
        //$this->SetTopMargin(120);
    }


    public function Footer($footer = "")
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $numPage = $this->getAliasNumPage();
        $nbPages = $this->getAliasNbPages();

        $this->writeHTML('<table><tr><td style="width:30%"></td><td style="width:30%; text-align: center">' . $this->commission . '</td><td style="width:30%; text-align: right">' . $numPage . '/' . $nbPages . '</td></tr></table>', false, true, false, true);
    }

    public function resetFooterText()
    {
        $this->commission = "";
    }

    /**
     * add an table html
     */
    public function addTable($titles, $data, $titre = NULL, $comText = "", $empty = "NEANT")
    {
        $this->commission = $comText;

        $j = 0;
        $header = implode("",  array_map(function ($h)  use (&$j) {
            // return "<th>" . ucfirst($h) . "</th>";
            $j += 1;

            if ($j == 1) {
                $buildH =  '<th style="width: 40px;">' . ucfirst($h) . "</th>";
            } else if ($j == 2 || $j == 3) {
                $buildH =  '<th style="width: 140px;">' . ucfirst($h) . "</th>";
            } else {
                $buildH =  "<th>" . ucfirst($h) . "</th>";
            }
            return $buildH;
        }, $titles));
        $rows =  '';

        foreach ($data as $line) {

            $tr = implode("",  array_map(function ($cell) use (&$i) {
                $i += 1;

                if ($i == 1) {
                    $buildtr = '<td style="width: 40px;">' . $cell . "</td>";
                } else if ($i == 2 || $i == 3) {
                    $buildtr = '<td style="width: 140px;">' . $cell . "</td>";
                } else {
                    $buildtr = "<td>" . $cell . "</td>";
                }
                return $buildtr;
            }, $line));

            $rows .= <<<EOD
                <tr style="text-align: left">
                    $tr 
                </tr> 
            EOD;
        }

        if ($rows !=  '') {
            $tbl = <<<EOD
                    <style> td:nth-of-type(0) { text-align: center; } th {font-weight: bold;}</style>
                    <div>
                    <table border="1" cellspacing="0" cellpadding="4">
                        <tr style="font-size: 11px; text-align: center">
                            $header
                        </tr>    
                        $rows
                    </table>
                    </div>
                EOD;
        } else {
            $tbl = <<<EOD
                    <style> td { text-align: left; } th {font-weight: bold;}</style>
                    <div style="text-align:center; font-size: 15px;">
                      $empty
                    </div>
                EOD;
        }

        if ($titre != NULL) {
            $this->writeHTML("<h3>$titre</h3>", true, false, true, false, 'C');
            $this->SetY($this->GetY() + 5);
        }
        $this->writeHTML($tbl, true, false, false, false, '');
    }
}

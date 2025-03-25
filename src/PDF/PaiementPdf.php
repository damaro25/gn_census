<?php

declare(strict_types=1);

namespace App\PDF;

use Symfony\Component\HttpKernel\KernelInterface;
use TCPDF;

class PaiementPdf extends TCPDF
{
    private static $X = 10;
    private static $Y = 90;


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
       // $this->setFooterData(array(0,64,0), array(0,64,128));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
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
        $this->AddPage("L");

        // $this->SetAutoPageBreak(TRUE, 0);

        // set text shadow effect
        $this->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // gambia
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/gambia.png', 10, 10, 70, 30, 'PNG');
        $this->writeHTML("<h3>N°……… MEPC/GBOS/DG/AC/SCF</h3> <br><br>Budget : RGPH <br><br> Financement : Etat", true, false, false, false, 'R');
        
        // set logo GBOS
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_grille.png', 10, 50, 40, 30, 'PNG');
        $img = @$this->Image($kernel->getProjectDir() . '/public/dist/img/logo_gphc5.png', 10, 50, 40, 30, 'PNG', '', 'T', false, 300, 'R', false, false, 0,     false, false, false);
        $this->setX(10);
        $this->setY(70);
        $this->write(25, "Je suis recensé (e), je compte !", '', false, "R");

        $this->setX(10);
        $this->setY(90);

        $this->writeHTML("<h2>${title}</h2>", true, false, false, false, 'C');
        $this->SetY($this->GetY()+5);
        // $this->setCellPaddings(0, , 0, 0);
        //$this->SetMargins(10, 55, 10, true);
        //$this->SetTopMargin(120);
    }


    public function Footer(){
        $this->SetY(-15);
        $this->SetFont('helvetica','I',8);
        $numPage=$this->getAliasNumPage();
        $nbPages=$this->getAliasNbPages();
       
        $this->writeHTML('<table><tr><td style="width:30%;font-size: 16px; font-weight: normal;"></td><td style="width:30%; text-align: center;font-size: 16px; font-weight: normal;">GBOS-RGPH-5</td><td style="width:30%; text-align: right;font-size: 16px; font-weight: normal;">'.$numPage.'/'.$nbPages.'</td></tr></table>', false, true, false, true);
    }
    
    /**
     * add an table html
     */
    public function addTable($titles, $data , $titre = NULL)
    {
        $header = implode("",  array_map(function ($h) {
            return "<th>" . ucfirst($h) . "</th>";
        }, $titles));
        $rows =  '';
        foreach ($data as $line) {

            $tr = implode("",  array_map(function ($cell) {
                return "<td>" . $cell . "</td>";
            }, $line));

            $rows .= <<<EOD
                <tr>
                    $tr 
                </tr> 
            EOD;
        }

        $tbl = <<<EOD
        <style> td { text-align: center; } th {font-weight: bold;}</style>
        <div>
           <table border="1" cellspacing="0" cellpadding="4">
              <tr style="font-size: 11px; text-align: center">
                $header
              </tr>    
              $rows
           </table>
        </div>
    EOD;
   
        if($titre != NULL){
            $this->writeHTML("<h3>$titre</h3>", true, false, true, false, 'C');
            $this->SetY($this->GetY()+5);
        }
       // $this->writeHTML($tbl, true, false, false, false, '');
    }
}
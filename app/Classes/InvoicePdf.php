<?php
// app/Classes/InvoicePdf.php
namespace App\Classes;

use TCPDF;

// Extend the TCPDF class to create custom Header and Footer
class InvoicePdf extends TCPDF
{

  //Page header
  public function Header()
  {
    global $id_facture, $date_facture, $date_echeance, $client_code;
    // Logo
    $image_file = 'logo-XDM.png';
    $this->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    // Set font
    $this->setFont('helvetica', 'B', 12);
    $this->Cell(0, 12, 'Facture XDM' . $id_facture, 0, true, 'R', 0, '', 0, false, 'M', 'M');

    $this->setFont('helvetica', 'N', 8);
    $this->Cell(0, 6, "Date facturation : $date_facture", 0, true, 'R', 0, '', 0, false, 'M', 'M');
    $this->Cell(0, 6, "Date échéance : $date_echeance", 0, true, 'R', 0, '', 0, false, 'M', 'M');
    $this->Cell(0, 6, 'Code client : ' . $client_code, 0, true, 'R', 0, '', 0, false, 'M', 'M');
  }

  // Page footer
  public function Footer()
  {
    // Position at 15 mm from bottom
    $this->setY(-15);
    // Set font
    $this->setFont('helvetica', 'N', 7);
    // Page number
    $this->Cell(0, 10, 'Société par actions simplifiée unipersonnelle (SASU) - Capital de 2 000 - SIREN: 823059699 ', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    $this->Cell(0, 10, $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
  }

  // Function to write a line with justification
  /* @param float $x X position
   * @param float $y Y position
   * @param float $h Height of the line
   * @param string $line Text to write
   * @param string $justify Justification ('L', 'C', 'R')
   * @return float New Y position after writing the line
   */
  function writeLine($x, $y, $h, $line, $justify)
  {

    if ($justify != 'R') {
      $this->SetXY($x, $y);
    }
    $this->Cell(0, $h, $line, 0, false, $justify, false);

    return $y + $h;
  }
}

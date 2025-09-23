<?php
// app/Classes/InvoicePdf.php
namespace App\Classes;

use TCPDF;

// Extend the TCPDF class to create custom Header and Footer
class InvoicePdf extends TCPDF
{
  protected $id_facture, $date_facture , $date_echeance, $client_code;

  // Pass data through the constructor or a method
  public function __construct($id, $date, $echeance, $code)
  {
    $this->id_facture = $id;
    $this->date_facture = $date;
    $this->date_echeance = $echeance;
    $this->client_code = $code;
    parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  }

  //Page header
  public function Header()
  {
    // Access data from the protected property
    $id_facture = $this->id_facture;
    $date_facture = $this->date_facture;
    $date_echeance = $this->date_echeance;
    $client_code = $this->client_code;

    // Logo
    $image_file = 'logo-XDM.png';
    $this->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    // Set font
    $this->setTitleFont();
    $this->Cell(0, 12, 'Facture ' . $id_facture, 0, true, 'R', 0, '', 0, false, 'M', 'M');

    $this->setFont('helvetica', 'N', 8);
    $this->Cell(0, 6, "Date facturation : $date_facture", 0, true, 'R', 0, '', 0, false, 'M', 'M');
    $this->Cell(0, 6, "Date échéance : $date_echeance", 0, true, 'R', 0, '', 0, false, 'M', 'M');
    $this->Cell(0, 6, 'Code client : ' . $client_code, 0, true, 'R', 0, '', 0, false, 'M', 'M');
  }

  public function setNormalFont()
  {
    $this->setFont('helvetica', 'N', 8);
    return 4;
  }
  public function setSubTitleFont()
  {
    $this->setFont('helvetica', 'B', 9);
    return 5;
  }
  public function setTitleFont()
  {
    $this->setFont('helvetica', 'B', 12);
    return 7;
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

  function prepare($id_facture, $x, $company, $client)
  {

    // Document info
    $this->SetCreator(PDF_CREATOR);
    $this->SetAuthor($company->name);
    $this->SetTitle("Facture $id_facture");
    $this->SetSubject('Facture');

    // set default header data
    $this->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

    // set header and footer fonts
    $this->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $this->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $this->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $this->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $this->setHeaderMargin(PDF_MARGIN_HEADER);
    $this->setFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $this->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $this->SetFillColor(230, 230, 230);
    $this->SetTextColor(0, 0, 0);

    // Add a page
    $this->AddPage();

    $this->SetFont('helvetica', '', 8);

    // Set position for box
    $x2 = 100; // Adjusted for the recipient box
    $y = 36;
    $w = 82;
    $h = 40;
    $invoiceY = $y + $h;

    $lineHeight = 6;
    $currentY = $y;

    $this->SetXY($x, $currentY);
    $this->Cell(0, $lineHeight, 'Émetteur', 0, false, 'L', false);

    $this->SetXY($x2 + 2, $currentY);
    $this->Cell($w, $lineHeight, 'Adressé à', 0, false, 'L', false);

    //$currentY = $y + 2;
    $currentY += $lineHeight;
    // Draw filled rectangle for background
    $this->SetFillColor(230, 230, 230);
    $this->SetLineWidth(0.1);
    $this->Rect($x, $currentY, $w, $h, 'F'); // 'F' = fill
    // Draw the border rectangle only (no fill)
    $this->Rect(100, $currentY, $w + 13, $h, 'D'); // 'D' = draw only (border)

    $currentY += 2;
    $this->SetFont('helvetica', 'B', 10);
    $this->SetXY($x + 2, $currentY);
    $this->Cell(0, $lineHeight, $company->name, 0, 1, 'L', false);

    $this->SetXY($x2 + 2, $currentY);
    $this->Cell(0, $lineHeight, $client->name, 0, 1, 'L', false);


    // Normal font for address
    $this->SetFont('helvetica', '', 9);
    $addressLines = [
      $company->address,
      $company->zip . " " . $company->city,
      $company->country,
      "Tél.: " . $company->phone,
      "Email: " . $company->email,
      "Web: " . $company->website
    ];
    $lineHeight = 4.5;
    $saveY = $currentY; // Save the current Y position
    foreach ($addressLines as $line) {
      $currentY += $lineHeight;
      $this->SetXY($x + 2, $currentY);
      $this->Cell(0, $lineHeight, $line, 0, 1, 'L', false);
    }


    $currentY = $saveY; // Reset Y position for client address
    $currentY += $lineHeight;
    if($client->address2 != null){
      $currentY += $lineHeight;
      $this->SetXY($x2 + 2, $currentY);
      $this->Cell(0, $lineHeight, $client->address2, 0, 1, 'L', false);
    }
    $currentY += $lineHeight;
    $this->SetXY($x2 + 2, $currentY);
    $this->Cell(0, $lineHeight, $client->address, 0, 1, 'L', false);
    $currentY += $lineHeight;
    $this->SetXY($x2 + 2, $currentY);
    $this->Cell(0, $lineHeight, $client->zip . " " . $client->city, 0, 1, 'L', false);
    $currentY += $lineHeight;
    $this->SetXY($x2 + 2, $currentY);
    $this->Cell(0, $lineHeight, $client->country, 0, 1, 'L', false);

    $this->SetFont('helvetica', '', 8);
    $currentY = $invoiceY + $lineHeight * 2;

    $line = "Catégorie d'opérations : Prestation de services";
    $currentY = $this->writeLine($x, $currentY,  $lineHeight, $line, 'L');
    $line = "Montants exprimés en Euros";
    $this->writeLine($x, $currentY,  $lineHeight, $line, 'R');

    return $currentY;
  }
}

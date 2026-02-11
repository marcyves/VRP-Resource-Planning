<?php

namespace App\Classes;

use TCPDF;
use App\Models\Company;
use App\Models\School;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceGenerator extends TCPDF
{
  protected $invoice;

  public function __construct(Invoice $invoice)
  {
    $this->invoice = $invoice;
    // On initialise avec les données de base de la facture
    parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $this->SetCreator(PDF_CREATOR);
    $this->SetTitle('Facture ' . $invoice->id);
    $this->SetAuthor(Auth::user()->name);
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
    $this->SetFont('helvetica', '', 8);
  }

  public function drawInvoiceContent(Company $company, School $school, $lines)
  {
    $this->SetFont('helvetica', '', 10);
    $x = 10;
    $y = 32;

    $lineHeight = 6;
    $h = 40;

    // Bloc Expéditeur (Company)
    $this->SetXY($x, $y);
    $this->drawCompanyBox($x, $y, $lineHeight, $h, $company);

    // Bloc Client (School)
    $this->SetXY($x + 100, $y);
    $this->drawSchoolBox($x, $y, $lineHeight, $h,  $school);

    // Draw the invoice details table
    $y = $y + $h;
    $y = $this->drawInvoiceDetailsTable($x, $y,  $lineHeight, $lines);

    // Draw RIB
    $this->drawRIB($y - 4, $company);
  }

  protected function drawCompanyBox($x, $y, $lineHeight, $h, $company)
  {
    $this->SetFont('helvetica', '', 8);

    // Set position for box
    $x2 = 100; // Adjusted for the recipient box
    $w = 82;

    $currentY = $y;

    $this->SetXY($x, $currentY);
    $this->Cell(0, $lineHeight, 'Émetteur', 0, false, 'L', false);

    $currentY += $lineHeight;
    // Draw filled rectangle for background
    $this->SetFillColor(230, 230, 230);
    $this->SetLineWidth(0.1);
    $this->Rect($x, $currentY, $w, $h, 'F'); // 'F' = fill

    $currentY += 2;
    $this->SetFont('helvetica', 'B', 10);
    $this->SetXY($x + 2, $currentY);
    $this->Cell(0, $lineHeight, $company->name, 0, 1, 'L', false);

    // Normal font for address
    $this->SetFont('helvetica', '', 9);
    $addressLines = [
      $company->address,
      $company->zip . " " . $company->city,
      $company->country,
      "Tél.: " . $company->phone,
      "Email: " . $company->email,
      "Web: " . $company->website,
      "N° de TVA: FR16823053699" //. $company->vat_number,
    ];

    $lineHeight = 4.5;
    foreach ($addressLines as $line) {
      $currentY += $lineHeight;
      $this->SetXY($x + 2, $currentY);
      $this->Cell(0, $lineHeight, $line, 0, 1, 'L', false);
    }
  }

  protected function drawSchoolBox($x, $y, $lineHeight, $h, $client)
  {
    $this->SetFont('helvetica', '', 8);
    $currentY = $y;
    // Set position for box
    $x2 = 100; // Adjusted for the recipient box
    $w = 82;

    $this->SetXY($x2 + 2, $currentY);
    $this->Cell($w, $lineHeight, 'Adressé à', 0, false, 'L', false);

    $currentY += $lineHeight;

    // Draw the border rectangle only (no fill)
    $this->Rect(100, $currentY, $w + 13, $h, 'D'); // 'D' = draw only (border)

    $currentY += 2;
    $this->SetFont('helvetica', 'B', 10);
    $this->SetXY($x2 + 2, $currentY);
    $this->Cell(0, $lineHeight, $client->name, 0, 1, 'L', false);

    // Normal font for address
    $this->SetFont('helvetica', '', 9);
    $currentY = $y; // Reset Y position for client address
    $currentY += $lineHeight;
    if ($client->address2 != null) {
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
  }

  protected function drawInvoiceDetailsTable($x, $y, $lineHeight, $lines)
  {
    // Title row
    $this->SetFont('helvetica', '', 8);
    $currentY = $y + $lineHeight;
    $currentY = $this->writeLine($x, $currentY,  $lineHeight, "Catégorie d'opérations : Prestation de services", 'L');
    $this->writeLine($x, $currentY,  $lineHeight, "Montants exprimés en Euros", 'R');

    // Draw box around table

    $x = 10;
    $wpage = 185;

    $lineHeight = 4;
    $invoice_lines = 35;

    // Draw the invoice details box
    $boxHeight = $lineHeight * 1.5;
    $this->Rect($x, $currentY, $wpage, $boxHeight, 'D'); // 'D' = draw only (border)

    $this->SetFont('helvetica', '', 9);
    $line = "Désignation";
    $this->writeLine($x, $currentY,  $boxHeight, $line, 'L');

    $x_col = $x + 107;
    $this->writeLine($x_col / 2 - 4, $currentY,  $boxHeight, "TVA", 'C');
    $this->Rect($x_col, $currentY, 16, $lineHeight * $invoice_lines + $boxHeight, 'D'); // 'D' = draw only (border)
    $this->writeLine($x_col / 2 + 34, $currentY,  $boxHeight, "P.U. HT", 'C');
    $this->writeLine($x_col / 2 + 74, $currentY,  $boxHeight, "Qté", 'C');
    $this->Rect($x_col + 40, $currentY, 16, $lineHeight * $invoice_lines + $boxHeight, 'D'); // 'D' = draw only (border)
    $currentY = $this->writeLine($x_col / 2 + 114, $currentY,  $boxHeight, "Total HT", 'C');

    // Draw the border for the invoice details box
    $this->Rect($x, $currentY, $wpage, $lineHeight * $invoice_lines, 'D'); // 'D' = draw only (border)


    $this->SetFont('helvetica', '', 9);

    $invoiceY = $currentY + 1; // Move down for the first item
    $total_invoice = 0;
    $invoice_line = 0;
    foreach ($lines as $item) {
      $invoice_line++;
      switch ($item[5]) {
        case "T":
          $lineHeight = $this->setTitleFont();
          break;
        case "S":
          $lineHeight = $this->setSubTitleFont();
          break;
        default:
          $lineHeight = $this->setNormalFont();
      }
      $this->SetXY($x + 2, $invoiceY);
      $this->Cell(108, $lineHeight, $item[0], 0, 0, 'L', false);

      $this->setNormalFont();
      $this->Cell(12, $lineHeight, $item[1], 0, 0, 'C', false);

      $rate = $item[2];
      if (is_numeric($rate))
        $this->Cell(18, $lineHeight, number_format($rate, 2), 0, 0, 'R', false);
      else
        $this->Cell(18, $lineHeight, $rate, 0, 0, 'R', false);

      $duration = $item[3];
      if (is_numeric($duration)) {
        $this->Cell(22, $lineHeight, number_format($duration, 2), 0, 0, 'R', false);
      } else
        $this->Cell(22, $lineHeight, $duration, 0, 0, 'R', false);


      if (is_numeric($rate) && is_numeric($duration)) {
        $total = $rate * $duration;
        $total_invoice += $total;
        $total = number_format($total, 2);
      } else {
        $total = "";
      }

      $this->Cell(20, $lineHeight, $total, 0, 1, 'R', false);
      $invoiceY += $lineHeight;
    }


    $currentY += $invoice_lines * $lineHeight + 2;
    // Add total line
    $this->SetFont('helvetica', 'B', 9);
    $this->writeLine($x, $currentY,  $lineHeight,  "Conditions de règlement:", 'L');
    $this->SetFont('helvetica', '', 9);
    $this->writeLine($x + 40, $currentY,  $lineHeight, "À réception", 'L');

    $this->writeLine($x + 120, $currentY, $lineHeight, "Total HT", 'L');
    $currentY = $this->writeLine($x + 140, $currentY, $lineHeight, number_format($this->invoice->amount, 2),  'R');
    $saveY = $currentY; // Save Y position for RIB
    $this->writeLine($x + 120, $currentY, $lineHeight, "Total TVA 20%",  'L');
    $currentY = $this->writeLine($x + 140, $currentY, $lineHeight, number_format($this->invoice->amount * 0.2, 2),  'R');
    $this->Rect($x + 120, $currentY, 60, $lineHeight, 'F'); // 'F' = fill
    $this->writeLine($x + 120, $currentY, $lineHeight, "Total TTC",  'L');
    $currentY = $this->writeLine($x + 140, $currentY, $lineHeight, number_format($this->invoice->amount * 1.2, 2),  'R');

    return $currentY;
  }

  protected function drawRIB($y, Company $company)
  {
    $this->SetFont('helvetica', 'B', 8);
    $x = 10;
    $lineHeight = 4;

    $RIB = [
      "Règlement par virement sur le compte bancaire suivant:",
      "",
      "Banque: " . $company->bank_name,
      "    Code banque      Code guichet       Numéro de compte     Clé",
      "            " . $company->bank . "                  " . $company->branch . "                  " . $company->account . "           " . $company->key,
      "Titulaire du compte: " . $company->iban_name,
      "Code IBAN: " . $company->iban,
      "Code BIC/SWIFT: " . $company->bic
    ];

    foreach ($RIB as $line) {
      $y = $this->writeLine($x, $y, $lineHeight, $line, 'L');
    }
  }

  /**
   * 
   */

  //Page header
  public function Header()
  {
    // Logo
    $image_file = 'logo-XDM.png';
    $this->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    // Set font
    $this->setTitleFont();
    $this->Cell(0, 12, 'Facture ' . $this->invoice->company->bill_prefix . $this->invoice->id, 0, true, 'R', 0, '', 0, false, 'M', 'M');

    $this->setFont('helvetica', 'N', 8);
    $this->Cell(0, 6, "Date facturation : " . date('d/m/Y', strtotime($this->invoice->bill_date)), 0, true, 'R', 0, '', 0, false, 'M', 'M');
    $this->Cell(0, 6, "Date échéance : " . $this->invoice->created_at->addDays(30)->format('d/m/Y'), 0, true, 'R', 0, '', 0, false, 'M', 'M');
    $this->Cell(0, 6, 'Code client : ' . $this->invoice->school->code, 0, true, 'R', 0, '', 0, false, 'M', 'M');
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
    $this->setY(-10);
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

    // If a page break occurred, GetY() will be reset to the top margin.
    // In that case, we should return the new Y + height.
    $new_y = $this->GetY();

    if ($new_y < $y) {
      return $new_y + $h;
    }

    return $y + $h;
  }
}

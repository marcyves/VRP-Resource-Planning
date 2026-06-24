<?php

namespace App\Services\ElectronicInvoicing;

use App\DTO\ElectronicInvoice\CiiTradeParty;
use App\Http\Utility\Tools;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use Carbon\Carbon;

/**
 * Génère un XML CII EN 16931 sans préfixes (format attendu par SuperPDP), converti en Factur-X côté PA.
 */
class ElectronicInvoiceCiiBuilder
{
    private const GUIDELINE = 'urn:cen.eu:en16931:2017';

    private const NS_RSM = 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100';

    private const NS_RAM = 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100';

    private const NS_UDT = 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100';

    public function build(Invoice $invoice, ?CiiTradeParty $sellerOverride = null, ?CiiTradeParty $buyerOverride = null): string
    {
        $invoice->loadMissing(['company', 'school']);

        $company = $invoice->company;
        $school = $invoice->school;

        if (! $company instanceof Company || ! $school instanceof School) {
            throw new \InvalidArgumentException('Invoice must have company and school.');
        }

        $seller = $sellerOverride ?? CiiTradeParty::fromCompany($company);
        $buyer = $buyerOverride ?? CiiTradeParty::fromSchool($school);

        $invoiceNumber = $company->bill_prefix.$invoice->id;
        $issueDate = Carbon::parse($invoice->bill_date);
        $lines = $this->resolveLines($invoice);
        $amountHt = round(array_sum(array_column($lines, 'line_total')), 2);
        $vatRate = 20.0;
        $vatAmount = round($amountHt * ($vatRate / 100), 2);
        $amountTtc = round($amountHt + $vatAmount, 2);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $root = $this->element($doc, self::NS_RSM, 'CrossIndustryInvoice');
        $doc->appendChild($root);

        $context = $this->element($doc, self::NS_RSM, 'ExchangedDocumentContext', $root);
        $process = $this->element($doc, self::NS_RAM, 'BusinessProcessSpecifiedDocumentContextParameter', $context);
        $this->text($doc, self::NS_RAM, 'ID', 'M1', $process);
        $guideline = $this->element($doc, self::NS_RAM, 'GuidelineSpecifiedDocumentContextParameter', $context);
        $this->text($doc, self::NS_RAM, 'ID', self::GUIDELINE, $guideline);

        $document = $this->element($doc, self::NS_RSM, 'ExchangedDocument', $root);
        $this->text($doc, self::NS_RAM, 'ID', $invoiceNumber, $document);
        $this->text($doc, self::NS_RAM, 'TypeCode', '380', $document);
        $issueDateTime = $this->element($doc, self::NS_RAM, 'IssueDateTime', $document);
        $this->dateTime($doc, $issueDate, $issueDateTime);
        $this->appendFrenchLegalNotes($doc, $document);

        $transaction = $this->element($doc, self::NS_RSM, 'SupplyChainTradeTransaction', $root);

        $lineIndex = 1;
        foreach ($lines as $line) {
            $this->appendLineItem($doc, $transaction, $lineIndex++, $line, $vatRate);
        }

        $agreement = $this->element($doc, self::NS_RAM, 'ApplicableHeaderTradeAgreement', $transaction);
        $this->appendTradeParty($doc, $agreement, 'SellerTradeParty', $seller);
        $this->appendTradeParty($doc, $agreement, 'BuyerTradeParty', $buyer);

        $this->appendHeaderDelivery($doc, $transaction, $buyer, $issueDate);

        $dueDate = $issueDate->copy()->addDay();
        $settlement = $this->element($doc, self::NS_RAM, 'ApplicableHeaderTradeSettlement', $transaction);
        $this->text($doc, self::NS_RAM, 'InvoiceCurrencyCode', 'EUR', $settlement);

        $headerTax = $this->element($doc, self::NS_RAM, 'ApplicableTradeTax', $settlement);
        $this->text($doc, self::NS_RAM, 'CalculatedAmount', $this->formatAmount($vatAmount), $headerTax);
        $this->text($doc, self::NS_RAM, 'TypeCode', 'VAT', $headerTax);
        $this->text($doc, self::NS_RAM, 'BasisAmount', $this->formatAmount($amountHt), $headerTax);
        $this->text($doc, self::NS_RAM, 'CategoryCode', 'S', $headerTax);
        $this->text($doc, self::NS_RAM, 'RateApplicablePercent', $this->formatAmount($vatRate), $headerTax);

        $paymentTerms = $this->element($doc, self::NS_RAM, 'SpecifiedTradePaymentTerms', $settlement);
        $dueDateTime = $this->element($doc, self::NS_RAM, 'DueDateDateTime', $paymentTerms);
        $this->dateTime($doc, $dueDate, $dueDateTime);

        $summation = $this->element($doc, self::NS_RAM, 'SpecifiedTradeSettlementHeaderMonetarySummation', $settlement);
        $this->text($doc, self::NS_RAM, 'LineTotalAmount', $this->formatAmount($amountHt), $summation);
        $this->text($doc, self::NS_RAM, 'TaxBasisTotalAmount', $this->formatAmount($amountHt), $summation);
        $taxTotal = $this->element($doc, self::NS_RAM, 'TaxTotalAmount', $summation);
        $taxTotal->setAttribute('currencyID', 'EUR');
        $taxTotal->appendChild($doc->createTextNode($this->formatAmount($vatAmount)));
        $this->text($doc, self::NS_RAM, 'GrandTotalAmount', $this->formatAmount($amountTtc), $summation);
        $this->text($doc, self::NS_RAM, 'DuePayableAmount', $this->formatAmount($amountTtc), $summation);

        return $doc->saveXML() ?: '';
    }

    /**
     * @return list<array{name: string, quantity: float, unit_price: float, line_total: float, unit_code: string}>
     */
    private function resolveLines(Invoice $invoice): array
    {
        $issueDate = Carbon::parse($invoice->bill_date);

        try {
            [$items] = Tools::getInvoiceDetails(
                (string) $invoice->school_id,
                (int) $issueDate->format('m'),
                (int) $issueDate->format('Y'),
                $invoice->company->bill_prefix.$invoice->id,
            );
        } catch (\Throwable) {
            $items = [];
        }

        $lines = [];

        foreach ($items as $item) {
            if (($item[5] ?? '') !== 'T') {
                continue;
            }

            $name = (string) ($item[0] ?? 'Prestation');
            $rate = (float) ($item[2] ?? 0);
            $hours = (float) ($item[3] ?? 0);
            $lineTotal = round($rate * $hours, 2);

            if ($lineTotal <= 0) {
                continue;
            }

            $lines[] = [
                'name' => $name,
                'quantity' => $hours > 0 ? $hours : 1.0,
                'unit_price' => $hours > 0 ? $rate : $lineTotal,
                'line_total' => $lineTotal,
                'unit_code' => $hours > 0 ? 'HUR' : 'C62',
            ];
        }

        if ($lines !== []) {
            return $lines;
        }

        $amountHt = round((float) $invoice->amount / 1.2, 2);

        return [[
            'name' => $invoice->description ?: 'Prestation',
            'quantity' => 1.0,
            'unit_price' => $amountHt,
            'line_total' => $amountHt,
            'unit_code' => 'C62',
        ]];
    }

    private function appendFrenchLegalNotes(\DOMDocument $doc, \DOMElement $document): void
    {
        $notes = [
            ['PMT', 'L’indemnité forfaitaire légale pour frais de recouvrement est de 40 €.'],
            ['PMD', 'À défaut de règlement à la date d’échéance, une pénalité de 10 % du net à payer sera applicable immédiatement.'],
            ['AAB', 'Aucun escompte pour paiement anticipé.'],
        ];

        foreach ($notes as [$code, $content]) {
            $note = $this->element($doc, self::NS_RAM, 'IncludedNote', $document);
            $this->text($doc, self::NS_RAM, 'Content', $content, $note);
            $this->text($doc, self::NS_RAM, 'SubjectCode', $code, $note);
        }
    }

    private function appendHeaderDelivery(\DOMDocument $doc, \DOMElement $transaction, CiiTradeParty $buyer, Carbon $issueDate): void
    {
        $delivery = $this->element($doc, self::NS_RAM, 'ApplicableHeaderTradeDelivery', $transaction);

        $shipTo = $this->element($doc, self::NS_RAM, 'ShipToTradeParty', $delivery);
        $address = $this->element($doc, self::NS_RAM, 'PostalTradeAddress', $shipTo);
        if ($buyer->address) {
            $this->text($doc, self::NS_RAM, 'LineOne', $buyer->address, $address);
        }
        if ($buyer->city) {
            $this->text($doc, self::NS_RAM, 'CityName', $buyer->city, $address);
        }
        if ($buyer->zip) {
            $this->text($doc, self::NS_RAM, 'PostcodeCode', $buyer->zip, $address);
        }
        $this->text($doc, self::NS_RAM, 'CountryID', $buyer->countryCode(), $address);

        $event = $this->element($doc, self::NS_RAM, 'ActualDeliverySupplyChainEvent', $delivery);
        $occurrence = $this->element($doc, self::NS_RAM, 'OccurrenceDateTime', $event);
        $this->dateTime($doc, $issueDate, $occurrence);
    }

    /**
     * @param  array{name: string, quantity: float, unit_price: float, line_total: float, unit_code: string}  $line
     */
    private function appendLineItem(\DOMDocument $doc, \DOMElement $transaction, int $index, array $line, float $vatRate): void
    {
        $item = $this->element($doc, self::NS_RAM, 'IncludedSupplyChainTradeLineItem', $transaction);

        $lineDoc = $this->element($doc, self::NS_RAM, 'AssociatedDocumentLineDocument', $item);
        $this->text($doc, self::NS_RAM, 'LineID', str_pad((string) $index, 3, '0', STR_PAD_LEFT), $lineDoc);

        $product = $this->element($doc, self::NS_RAM, 'SpecifiedTradeProduct', $item);
        $this->text($doc, self::NS_RAM, 'Name', $line['name'], $product);

        $agreement = $this->element($doc, self::NS_RAM, 'SpecifiedLineTradeAgreement', $item);
        $price = $this->element($doc, self::NS_RAM, 'NetPriceProductTradePrice', $agreement);
        $this->text($doc, self::NS_RAM, 'ChargeAmount', $this->formatAmount($line['unit_price']), $price);

        $delivery = $this->element($doc, self::NS_RAM, 'SpecifiedLineTradeDelivery', $item);
        $quantity = $this->element($doc, self::NS_RAM, 'BilledQuantity', $delivery);
        $quantity->setAttribute('unitCode', $line['unit_code']);
        $quantity->appendChild($doc->createTextNode($this->formatAmount($line['quantity'])));

        $settlement = $this->element($doc, self::NS_RAM, 'SpecifiedLineTradeSettlement', $item);
        $tax = $this->element($doc, self::NS_RAM, 'ApplicableTradeTax', $settlement);
        $this->text($doc, self::NS_RAM, 'TypeCode', 'VAT', $tax);
        $this->text($doc, self::NS_RAM, 'CategoryCode', 'S', $tax);
        $this->text($doc, self::NS_RAM, 'RateApplicablePercent', $this->formatAmount($vatRate), $tax);

        $monetary = $this->element($doc, self::NS_RAM, 'SpecifiedTradeSettlementLineMonetarySummation', $settlement);
        $this->text($doc, self::NS_RAM, 'LineTotalAmount', $this->formatAmount($line['line_total']), $monetary);
    }

    private function appendTradeParty(\DOMDocument $doc, \DOMElement $parent, string $tag, CiiTradeParty $party): void
    {
        $tradeParty = $this->element($doc, self::NS_RAM, $tag, $parent);
        $siren = $party->siren;

        if ($party->isBuyer && $siren) {
            $globalId = $this->element($doc, self::NS_RAM, 'GlobalID', $tradeParty);
            $globalId->setAttribute('schemeID', '0225');
            $globalId->appendChild($doc->createTextNode($siren));
        }

        $this->text($doc, self::NS_RAM, 'Name', $party->name, $tradeParty);

        if ($siren) {
            $legal = $this->element($doc, self::NS_RAM, 'SpecifiedLegalOrganization', $tradeParty);
            $id = $this->element($doc, self::NS_RAM, 'ID', $legal);
            $id->setAttribute('schemeID', '0002');
            $id->appendChild($doc->createTextNode($siren));
        }

        $address = $this->element($doc, self::NS_RAM, 'PostalTradeAddress', $tradeParty);
        if ($party->address) {
            $this->text($doc, self::NS_RAM, 'LineOne', $party->address, $address);
        }
        if ($party->city) {
            $this->text($doc, self::NS_RAM, 'CityName', $party->city, $address);
        }
        if ($party->zip) {
            $this->text($doc, self::NS_RAM, 'PostcodeCode', $party->zip, $address);
        }
        $this->text($doc, self::NS_RAM, 'CountryID', $party->countryCode(), $address);

        $electronicAddress = $party->resolvedElectronicAddress();
        if ($electronicAddress) {
            $uri = $this->element($doc, self::NS_RAM, 'URIUniversalCommunication', $tradeParty);
            $uriId = $this->element($doc, self::NS_RAM, 'URIID', $uri);
            $uriId->setAttribute('schemeID', '0225');
            $uriId->appendChild($doc->createTextNode($electronicAddress));
        }

        $vatNumber = $party->resolvedVatNumber();
        if ($vatNumber) {
            $taxReg = $this->element($doc, self::NS_RAM, 'SpecifiedTaxRegistration', $tradeParty);
            $vatId = $this->element($doc, self::NS_RAM, 'ID', $taxReg);
            $vatId->setAttribute('schemeID', 'VA');
            $vatId->appendChild($doc->createTextNode($vatNumber));
        }
    }

    private function element(\DOMDocument $doc, string $namespace, string $name, ?\DOMElement $parent = null): \DOMElement
    {
        $element = $doc->createElementNS($namespace, $name);
        $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $namespace);
        $parent?->appendChild($element);

        return $element;
    }

    private function text(\DOMDocument $doc, string $namespace, string $name, string $value, \DOMElement $parent): \DOMElement
    {
        $element = $this->element($doc, $namespace, $name, $parent);
        $element->appendChild($doc->createTextNode($value));

        return $element;
    }

    private function dateTime(\DOMDocument $doc, Carbon $date, \DOMElement $parent): void
    {
        $element = $this->element($doc, self::NS_UDT, 'DateTimeString', $parent);
        $element->setAttribute('format', '102');
        $element->appendChild($doc->createTextNode($date->format('Ymd')));
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}

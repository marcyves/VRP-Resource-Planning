<?php

namespace App\Services\BankStatement;

use RuntimeException;
use ZipArchive;

class XlsxSheetReader
{
    /**
     * @return array<int, array<string, string>>
     */
    public function readFirstSheet(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException(__('messages.bank_import_invalid_file'));
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException(__('messages.bank_import_invalid_file'));
        }

        return $this->parseSheetRows($sheetXml, $sharedStrings);
    }

    /**
     * @return list<string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);
        if ($shared === false) {
            return [];
        }

        $shared->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];

        foreach ($shared->si as $si) {
            $si->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $parts = $si->xpath('.//m:t') ?: [];
            $text = '';
            foreach ($parts as $part) {
                $text .= (string) $part;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseSheetRows(string $sheetXml, array $sharedStrings): array
    {
        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false) {
            return [];
        }

        $sheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $rowIndex = (int) ($row['r'] ?? 0);
            $cells = [];

            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $column = preg_replace('/\d+/', '', $ref) ?? '';
                $cells[$column] = $this->cellValue($cell, $sharedStrings);
            }

            if ($cells !== []) {
                $rows[$rowIndex] = $cells;
            }
        }

        ksort($rows);

        return $rows;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');
        $value = (string) ($cell->v ?? '');

        if ($type === 's' && $value !== '') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'inlineStr') {
            $cell->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $parts = $cell->xpath('.//m:t') ?: [];

            return implode('', array_map('strval', $parts));
        }

        return $value;
    }
}

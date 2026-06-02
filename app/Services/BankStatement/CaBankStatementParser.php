<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class CaBankStatementParser
{
    public function __construct(
        private readonly XlsxSheetReader $reader = new XlsxSheetReader,
    ) {}

    /**
     * @return array{
     *     account_number: ?string,
     *     account_label: ?string,
     *     period_start: ?string,
     *     period_end: ?string,
     *     statement_balance: ?float,
     *     lines: list<array{operation_date: string, label: string, debit: float, credit: float, amount: float, row_index: int}>
     * }
     */
    public function parse(string $path): array
    {
        $rows = $this->reader->readFirstSheet($path);
        $headerRow = $this->findHeaderRow($rows);

        if ($headerRow === null) {
            throw new RuntimeException(__('messages.bank_import_format_unrecognized'));
        }

        $meta = $this->parseMetadata($rows, $headerRow);
        $lines = [];

        foreach ($rows as $rowIndex => $cells) {
            if ($rowIndex <= $headerRow) {
                continue;
            }

            $line = $this->parseLine($cells, $rowIndex);
            if ($line !== null) {
                $lines[] = $line;
            }
        }

        if ($lines === []) {
            throw new RuntimeException(__('messages.bank_import_no_operations'));
        }

        $meta['lines'] = $lines;

        return $meta;
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     */
    private function findHeaderRow(array $rows): ?int
    {
        foreach ($rows as $rowIndex => $cells) {
            $normalized = array_map(fn ($v) => Str::lower(trim($v)), $cells);
            if (in_array('date', $normalized, true)
                && (in_array('libellé', $normalized, true) || in_array('libelle', $normalized, true))
                && (in_array('débit euros', $normalized, true) || in_array('debit euros', $normalized, true))
            ) {
                return $rowIndex;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array{account_number: ?string, account_label: ?string, period_start: ?string, period_end: ?string, statement_balance: ?float, lines: list<array>}
     */
    private function parseMetadata(array $rows, int $headerRow): array
    {
        $accountNumber = null;
        $accountLabel = null;
        $periodStart = null;
        $periodEnd = null;
        $statementBalance = null;

        foreach ($rows as $rowIndex => $cells) {
            if ($rowIndex >= $headerRow) {
                break;
            }

            $text = trim(implode(' ', array_values($cells)));

            if ($accountNumber === null && preg_match('/compte\s+courant\s+n[°º]?\s*([0-9]+)/iu', $text, $m)) {
                $accountNumber = $m[1];
            }

            if ($accountLabel === null && preg_match('/S\.?A\.?S\.?/iu', $text)) {
                $accountLabel = trim(preg_replace('/\s+/', ' ', $text));
            }

            if ($statementBalance === null && Str::contains(Str::lower($text), 'solde au')) {
                $statementBalance = $this->parseAmount($cells['C'] ?? $cells['B'] ?? '');
            }

            if (preg_match('/entre\s+le\s+(\d{2}\/\d{2}\/\d{4})\s+et\s+le\s+(\d{2}\/\d{2}\/\d{4})/iu', $text, $m)) {
                $periodStart = Carbon::createFromFormat('d/m/Y', $m[1])->toDateString();
                $periodEnd = Carbon::createFromFormat('d/m/Y', $m[2])->toDateString();
            }
        }

        return [
            'account_number' => $accountNumber,
            'account_label' => $accountLabel,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'statement_balance' => $statementBalance,
            'lines' => [],
        ];
    }

    /**
     * @param  array<string, string>  $cells
     * @return array{operation_date: string, label: string, debit: float, credit: float, amount: float, row_index: int}|null
     */
    private function parseLine(array $cells, int $rowIndex): ?array
    {
        $dateRaw = trim($cells['A'] ?? '');
        $label = trim(str_replace("\n", ' ', $cells['B'] ?? ''));
        $debit = $this->parseAmount($cells['C'] ?? '');
        $credit = $this->parseAmount($cells['D'] ?? '');

        if ($label === '' || ($debit <= 0 && $credit <= 0)) {
            return null;
        }

        $date = $this->parseDate($dateRaw);
        if ($date === null) {
            return null;
        }

        $amount = $credit > 0 ? $credit : -$debit;

        return [
            'operation_date' => $date,
            'label' => $label,
            'debit' => $debit,
            'credit' => $credit,
            'amount' => $amount,
            'row_index' => $rowIndex,
        ];
    }

    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{5}(\.\d+)?$/', $value)) {
            $serial = (float) $value;
            $days = (int) floor($serial);
            $date = Carbon::create(1899, 12, 30)->addDays($days);

            return $date->toDateString();
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
            return Carbon::createFromFormat('d/m/Y', $m[0])->toDateString();
        }

        return null;
    }

    private function parseAmount(string $value): float
    {
        $value = trim($value);
        if ($value === '') {
            return 0.0;
        }

        $value = str_replace(["\u{00A0}", "\u{202F}", ' '], '', $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value) ?? '';

        return $value === '' ? 0.0 : round((float) $value, 2);
    }
}

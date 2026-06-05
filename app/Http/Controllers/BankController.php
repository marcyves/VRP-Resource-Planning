<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use App\Services\BankStatement\BankReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BankController extends Controller
{
    public function __construct(
        private readonly BankReconciliationService $reconciliationService,
    ) {}

    public function index(Request $request)
    {
        $company = Auth::user()->company;
        $company->load('billingBankAccount.bank');
        $companyId = $company->id;

        $banks = Bank::where('company_id', $companyId)
            ->with(['accounts' => fn ($q) => $q->withCount('imports')->orderBy('account_number')])
            ->orderBy('name')
            ->get();

        $selectedBank = null;
        if ($request->filled('bank')) {
            $selectedBank = $banks->firstWhere('id', (int) $request->query('bank'));
        } elseif ($banks->count() === 1) {
            $selectedBank = $banks->first();
        }

        $importsQuery = BankStatementImport::where('company_id', $companyId)
            ->with('bankAccount.bank')
            ->withCount([
                'lines',
                'lines as reconciled_lines_count' => fn ($q) => $q->fullyReconciled(),
            ]);

        if ($selectedBank) {
            $accountIds = $selectedBank->accounts->pluck('id');
            $importsQuery->whereIn('bank_account_id', $accountIds);
        }

        $imports = $importsQuery->orderByDesc('created_at')->get();

        return view('treasury.bank.index', compact('banks', 'imports', 'company', 'selectedBank'));
    }

    public function updateBillingAccount(Request $request)
    {
        $company = Auth::user()->company;
        $companyId = $company->id;

        $validated = $request->validate([
            'billing_bank_account_id' => [
                'nullable',
                Rule::exists('bank_accounts', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'bank' => [
                'nullable',
                Rule::exists('banks', 'id')->where('company_id', $companyId),
            ],
        ]);

        $company->billing_bank_account_id = $validated['billing_bank_account_id'] ?? null;
        $company->save();

        session()->flash('success', __('messages.billing_bank_account_updated'));

        return redirect()->route('treasury.bank.index', array_filter([
            'bank' => $validated['bank'] ?? null,
        ]));
    }

    public function storeBank(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('banks', 'name')->where('company_id', $companyId),
            ],
        ]);

        $bank = Bank::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
        ]);

        session()->flash('success', __('messages.bank_created'));

        return redirect()->route('treasury.bank.index', ['bank' => $bank->id]);
    }

    public function updateBank(Request $request, Bank $bank)
    {
        $this->authorizeBank($bank);
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('banks', 'name')
                    ->where('company_id', $companyId)
                    ->ignore($bank->id),
            ],
        ]);

        $bank->update(['name' => $validated['name']]);

        session()->flash('success', __('messages.bank_updated'));

        return redirect()->route('treasury.bank.index', ['bank' => $bank->id]);
    }

    public function destroyBank(Bank $bank)
    {
        $this->authorizeBank($bank);

        if ($bank->accounts()->exists()) {
            session()->flash('danger', __('messages.bank_delete_has_accounts'));

            return redirect()->route('treasury.bank.index', ['bank' => $bank->id]);
        }

        $bank->delete();

        session()->flash('success', __('messages.bank_deleted'));

        return redirect()->route('treasury.bank.index');
    }

    public function storeAccount(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'bank_id' => [
                'required',
                Rule::exists('banks', 'id')->where('company_id', $companyId),
            ],
            'account_number' => 'required|string|max:64',
            'label' => 'nullable|string|max:255',
            'opening_date' => 'nullable|date',
            'opening_amount' => 'nullable|numeric',
            'iban_holder' => 'nullable|string|max:255',
            'rib_bank_code' => 'nullable|string|max:10',
            'rib_branch_code' => 'nullable|string|max:10',
            'rib_account_number' => 'nullable|string|max:20',
            'rib_key' => 'nullable|string|max:5',
            'iban' => 'nullable|string|max:50',
            'bic' => 'nullable|string|max:20',
        ]);

        $bank = Bank::where('company_id', $companyId)->findOrFail($validated['bank_id']);

        $exists = BankAccount::where('bank_id', $bank->id)
            ->where('account_number', $validated['account_number'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('treasury.bank.index', ['bank' => $bank->id])
                ->withInput()
                ->withErrors(['account_number' => __('messages.bank_account_exists')]);
        }

        BankAccount::create([
            'bank_id' => $bank->id,
            'company_id' => $companyId,
            'account_number' => $validated['account_number'],
            'label' => $validated['label'] ?? null,
            'iban_holder' => $validated['iban_holder'] ?? null,
            'rib_bank_code' => $validated['rib_bank_code'] ?? null,
            'rib_branch_code' => $validated['rib_branch_code'] ?? null,
            'rib_account_number' => $validated['rib_account_number'] ?? null,
            'rib_key' => $validated['rib_key'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'bic' => $validated['bic'] ?? null,
            'opening_date' => $validated['opening_date'] ?? null,
            'opening_amount' => $validated['opening_amount'] ?? 0,
        ]);

        session()->flash('success', __('messages.bank_account_created'));

        return redirect()->route('treasury.bank.index', ['bank' => $bank->id]);
    }

    public function updateAccount(Request $request, BankAccount $account)
    {
        $this->authorizeAccount($account);

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'opening_date' => 'nullable|date',
            'opening_amount' => 'nullable|numeric',
            'iban_holder' => 'nullable|string|max:255',
            'rib_bank_code' => 'nullable|string|max:10',
            'rib_branch_code' => 'nullable|string|max:10',
            'rib_account_number' => 'nullable|string|max:20',
            'rib_key' => 'nullable|string|max:5',
            'iban' => 'nullable|string|max:50',
            'bic' => 'nullable|string|max:20',
            'opening_date' => 'nullable|date',
            'opening_amount' => 'nullable|numeric',
            'active' => 'nullable|boolean',
        ]);

        $account->update([
            'label' => $validated['label'] ?? $account->label,
            'opening_date' => $validated['opening_date'] ?? $account->opening_date,
            'opening_amount' => $validated['opening_amount'] ?? $account->opening_amount,
            'iban_holder' => $validated['iban_holder'] ?? $account->iban_holder,
            'rib_bank_code' => $validated['rib_bank_code'] ?? $account->rib_bank_code,
            'rib_branch_code' => $validated['rib_branch_code'] ?? $account->rib_branch_code,
            'rib_account_number' => $validated['rib_account_number'] ?? $account->rib_account_number,
            'rib_key' => $validated['rib_key'] ?? $account->rib_key,
            'iban' => $validated['iban'] ?? $account->iban,
            'bic' => $validated['bic'] ?? $account->bic,
            'opening_date' => $validated['opening_date'] ?? $account->opening_date,
            'opening_amount' => $validated['opening_amount'] ?? $account->opening_amount,
            'active' => $request->boolean('active', $account->active),
        ]);

        session()->flash('success', __('messages.bank_account_updated'));

        return redirect()->route('treasury.bank.index', ['bank' => $account->bank_id]);
    }

    public function destroyAccount(BankAccount $account)
    {
        $this->authorizeAccount($account);

        $bankId = $account->bank_id;

        $this->reconciliationService->deleteAccount($account);

        session()->flash('success', __('messages.bank_account_deleted'));

        return redirect()->route('treasury.bank.index', ['bank' => $bankId]);
    }

    public function destroyImport(BankStatementImport $import)
    {
        $this->authorizeImport($import);

        $bankId = $import->bankAccount?->bank_id;

        $this->reconciliationService->deleteImport($import);

        session()->flash('success', __('messages.bank_import_deleted'));

        return redirect()->route('treasury.bank.index', array_filter([
            'bank' => $bankId,
        ]));
    }

    public function storeImport(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'bank_account_id' => [
                'required',
                Rule::exists('bank_accounts', 'id')->where('company_id', $companyId),
            ],
            'statement_file' => 'required|file|mimes:xlsx|max:10240',
        ]);

        $account = BankAccount::where('company_id', $companyId)
            ->with('bank')
            ->findOrFail($validated['bank_account_id']);

        try {
            $import = $this->reconciliationService->import(
                $validated['statement_file'],
                $account,
                Auth::id(),
            );

            session()->flash('success', __('messages.bank_import_success', [
                'count' => $import->lines_count,
            ]));

            return redirect()->route('treasury.bank.imports.show', $import);
        } catch (\Throwable $e) {
            session()->flash('danger', $e->getMessage());

            return redirect()->route('treasury.bank.index', ['bank' => $account->bank_id]);
        }
    }

    public function show(Request $request, BankStatementImport $import)
    {
        $this->authorizeImport($import);

        $import->load([
            'bankAccount.bank',
            'lines.reconciliations.reconcilable.school',
        ]);

        [$lines, $sort, $direction, $reconciledFilter] = $this->filteredStatementLines($import, $request);

        $suggestions = [];

        foreach ($lines as $line) {
            if (! $line->isReconciled()) {
                $suggestions[$line->id] = $this->reconciliationService->matchCandidates($line, $import->company_id);
            }
        }

        return view('treasury.bank.show', compact(
            'import',
            'lines',
            'suggestions',
            'sort',
            'direction',
            'reconciledFilter',
        ));
    }

    public function match(Request $request, BankStatementImport $import, BankStatementLine $line)
    {
        $this->authorizeImport($import);
        abort_unless($line->bank_statement_import_id === $import->id, 404);

        $validated = $request->validate([
            'match_ref' => 'required|string',
        ]);

        try {
            if (preg_match('/^invoices:(.+)$/', $validated['match_ref'], $parts)) {
                $invoiceIds = array_filter(explode(',', $parts[1]));
                $reconciliations = $this->reconciliationService->matchInvoices($line, $invoiceIds, $import->company_id);
                session()->flash('success', __('messages.bank_match_invoices_success', [
                    'count' => $reconciliations->count(),
                ]));
            } elseif (preg_match('/^(invoice|expense|expense_report):(.+)$/', $validated['match_ref'], $parts)) {
                $this->reconciliationService->match(
                    $line,
                    $parts[1],
                    $parts[2],
                    $import->company_id,
                );
                session()->flash('success', __('messages.bank_match_success'));
            } else {
                session()->flash('danger', __('messages.bank_match_invalid_type'));

                return redirect()->back();
            }
        } catch (\Throwable $e) {
            session()->flash('danger', $e->getMessage());
        }

        return redirect()->back();
    }

    public function unmatch(BankStatementImport $import, BankReconciliation $reconciliation)
    {
        $this->authorizeImport($import);
        abort_unless($reconciliation->company_id === $import->company_id, 404);

        $line = $reconciliation->line;
        abort_unless($line && $line->bank_statement_import_id === $import->id, 404);

        $this->reconciliationService->unmatch($reconciliation);

        session()->flash('success', __('messages.bank_unmatch_success'));

        return redirect()->back();
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, BankStatementLine>, 1: string, 2: string, 3: string}
     */
    private function filteredStatementLines(BankStatementImport $import, Request $request): array
    {
        $sort = in_array($request->query('sort'), ['date', 'label'], true)
            ? $request->query('sort')
            : 'date';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $reconciledFilter = in_array($request->query('reconciled'), ['reconciled', 'unreconciled'], true)
            ? $request->query('reconciled')
            : 'all';

        $lines = $import->lines;

        if ($reconciledFilter === 'reconciled') {
            $lines = $lines->filter(fn (BankStatementLine $line) => $line->isReconciled());
        } elseif ($reconciledFilter === 'unreconciled') {
            $lines = $lines->filter(fn (BankStatementLine $line) => ! $line->isReconciled());
        }

        $lines = $lines
            ->sort(function (BankStatementLine $a, BankStatementLine $b) use ($sort, $direction) {
                $cmp = match ($sort) {
                    'label' => strcasecmp($a->label, $b->label)
                        ?: ($a->operation_date <=> $b->operation_date),
                    default => $a->operation_date <=> $b->operation_date
                        ?: $a->row_index <=> $b->row_index,
                };

                return $direction === 'desc' ? -$cmp : $cmp;
            })
            ->values();

        return [$lines, $sort, $direction, $reconciledFilter];
    }

    private function authorizeBank(Bank $bank): void
    {
        abort_unless($bank->company_id === Auth::user()->company_id, 403);
    }

    private function authorizeAccount(BankAccount $account): void
    {
        abort_unless($account->company_id === Auth::user()->company_id, 403);
    }

    private function authorizeImport(BankStatementImport $import): void
    {
        abort_unless($import->company_id === Auth::user()->company_id, 403);
    }
}

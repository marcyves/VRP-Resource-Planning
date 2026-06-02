<?php

namespace App\Http\Controllers;

use App\Models\BankReconciliation;
use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use App\Services\BankStatement\BankReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankReconciliationController extends Controller
{
    public function __construct(
        private readonly BankReconciliationService $reconciliationService,
    ) {}

    public function index()
    {
        $companyId = Auth::user()->company_id;

        $imports = BankStatementImport::where('company_id', $companyId)
            ->withCount([
                'lines',
                'lines as reconciled_lines_count' => fn ($q) => $q->whereHas('reconciliation'),
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('treasury.reconciliation.index', compact('imports'));
    }

    public function storeImport(Request $request)
    {
        $validated = $request->validate([
            'statement_file' => 'required|file|mimes:xlsx|max:10240',
        ]);

        try {
            $import = $this->reconciliationService->import(
                $validated['statement_file'],
                Auth::user()->company_id,
                Auth::id(),
            );

            session()->flash('success', __('messages.bank_import_success', [
                'count' => $import->lines_count,
            ]));

            return redirect()->route('treasury.reconciliation.show', $import);
        } catch (\Throwable $e) {
            session()->flash('danger', $e->getMessage());

            return redirect()->route('treasury.reconciliation.index');
        }
    }

    public function show(BankStatementImport $import)
    {
        $this->authorizeImport($import);

        $import->load([
            'lines.reconciliation.reconcilable',
        ]);

        $lines = $import->lines;
        $suggestions = [];

        foreach ($lines as $line) {
            if (! $line->isReconciled()) {
                $suggestions[$line->id] = $this->reconciliationService->matchCandidates($line, $import->company_id);
            }
        }

        return view('treasury.reconciliation.show', compact('import', 'lines', 'suggestions'));
    }

    public function match(Request $request, BankStatementImport $import, BankStatementLine $line)
    {
        $this->authorizeImport($import);
        abort_unless($line->bank_statement_import_id === $import->id, 404);

        $validated = $request->validate([
            'match_ref' => 'required|string',
        ]);

        if (! preg_match('/^(invoice|expense|expense_report):(.+)$/', $validated['match_ref'], $parts)) {
            session()->flash('danger', __('messages.bank_match_invalid_type'));

            return redirect()->route('treasury.reconciliation.show', $import);
        }

        try {
            $this->reconciliationService->match(
                $line,
                $parts[1],
                $parts[2],
                $import->company_id,
            );

            session()->flash('success', __('messages.bank_match_success'));
        } catch (\Throwable $e) {
            session()->flash('danger', $e->getMessage());
        }

        return redirect()->route('treasury.reconciliation.show', $import);
    }

    public function unmatch(BankStatementImport $import, BankReconciliation $reconciliation)
    {
        $this->authorizeImport($import);
        abort_unless($reconciliation->company_id === $import->company_id, 404);

        $line = $reconciliation->line;
        abort_unless($line && $line->bank_statement_import_id === $import->id, 404);

        $this->reconciliationService->unmatch($reconciliation);

        session()->flash('success', __('messages.bank_unmatch_success'));

        return redirect()->route('treasury.reconciliation.show', $import);
    }

    private function authorizeImport(BankStatementImport $import): void
    {
        abort_unless($import->company_id === Auth::user()->company_id, 403);
    }
}

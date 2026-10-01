<?php

namespace App\Http\Controllers;

use App\Services\GroupFollowUpQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupFollowUpController extends Controller
{
    public function index(Request $request, GroupFollowUpQuery $query): View
    {
        $viewer = $request->user();

        if ($viewer === null || ! $viewer->canViewGroupFollowUp()) {
            abort(403);
        }

        $validated = $request->validate([
            'school_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $payload = $query->forViewer($viewer, $validated);

        return view('group-follow-up.index', [
            ...$payload,
            'filters' => [
                'school_id' => isset($validated['school_id']) ? (string) $validated['school_id'] : '',
                'q' => $validated['q'] ?? '',
                'from' => $validated['from'] ?? '',
                'to' => $validated['to'] ?? '',
            ],
            'formAction' => route('group-follow-up.index'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\LandingVisitRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingVisitCtaController extends Controller
{
    public function store(Request $request, LandingVisitRecorder $recorder): JsonResponse
    {
        $validated = $request->validate([
            'cta' => ['required', 'in:login,register,pricing'],
        ]);

        $recorder->markCta($request, $validated['cta']);

        return response()->json(['ok' => true]);
    }
}

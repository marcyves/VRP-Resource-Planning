<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($user = $request->user()) {
            return redirect($user->homePath());
        }

        return view('welcome');
    }

    public function locale(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('terminology.base_locales', ['fr', 'en', 'it']), true), 404);

        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: route('welcome'));
    }
}

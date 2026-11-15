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

    public function product(): View
    {
        return view('marketing.product', [
            'title' => __('messages.landing_product_title'),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'title' => __('messages.landing_pricing_title'),
        ]);
    }

    public function about(): View
    {
        return view('marketing.about', [
            'title' => __('messages.landing_about_title'),
        ]);
    }

    public function legal(): View
    {
        return view('marketing.legal', [
            'title' => __('messages.landing_legal_title'),
        ]);
    }

    public function locale(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('terminology.base_locales', ['fr', 'en', 'it']), true), 404);

        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: route('welcome'));
    }
}

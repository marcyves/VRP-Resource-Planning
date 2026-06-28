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
}

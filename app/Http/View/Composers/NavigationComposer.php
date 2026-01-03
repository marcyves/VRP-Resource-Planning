<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NavigationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $schools = Auth::user()->getSchools();
            // getYears now includes current and next year even if empty thanks to our Model fix
            $years = $schools->getYears();
            $view->with('years', $years);
        }
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default terminology profile
    |--------------------------------------------------------------------------
    |
    | Used when a company has no profile set. "education" = écoles / formation,
    | "consulting" = clients / projets / phases.
    |
    */

    'default_profile' => env('TERMINOLOGY_PROFILE', 'education'),

    'profiles' => [
        'education',
        'consulting',
        'medical',
    ],

    /*
    |--------------------------------------------------------------------------
    | Base locales (language only)
    |--------------------------------------------------------------------------
    */

    'base_locales' => ['fr', 'en', 'it'],

    'fallback_consulting_locale' => 'en_consulting',

    'fallback_medical_locale' => 'fr_medical',

];

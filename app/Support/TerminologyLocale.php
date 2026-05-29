<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Support\Facades\File;

class TerminologyLocale
{
    public const PROFILE_EDUCATION = 'education';

    public const PROFILE_CONSULTING = 'consulting';

    public static function resolve(?Company $company = null): string
    {
        $base = self::normalizeBaseLocale(config('app.locale', 'fr'));
        $profile = $company?->terminology_profile
            ?? config('terminology.default_profile', self::PROFILE_EDUCATION);

        if ($profile === self::PROFILE_CONSULTING) {
            $consultingLocale = "{$base}_consulting";

            if (File::isDirectory(lang_path($consultingLocale))) {
                return $consultingLocale;
            }

            return config('terminology.fallback_consulting_locale', 'en_consulting');
        }

        return $base;
    }

    public static function normalizeBaseLocale(string $locale): string
    {
        $base = explode('_', $locale)[0];

        if (in_array($base, config('terminology.base_locales', ['fr', 'en', 'it']), true)) {
            return $base;
        }

        return 'fr';
    }
}

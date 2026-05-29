<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Support\TerminologyLocale;
use Tests\TestCase;

class TerminologyLocaleTest extends TestCase
{
    public function test_education_profile_uses_base_locale(): void
    {
        config(['app.locale' => 'fr']);

        $company = new Company([
            'terminology_profile' => Company::PROFILE_EDUCATION,
        ]);

        $this->assertSame('fr', TerminologyLocale::resolve($company));
    }

    public function test_consulting_profile_uses_consulting_locale(): void
    {
        config(['app.locale' => 'fr']);

        $company = new Company([
            'terminology_profile' => Company::PROFILE_CONSULTING,
        ]);

        $this->assertSame('fr_consulting', TerminologyLocale::resolve($company));
    }

    public function test_legacy_en_proj_normalizes_to_en_consulting(): void
    {
        config(['app.locale' => 'en_proj']);

        $company = new Company([
            'terminology_profile' => Company::PROFILE_CONSULTING,
        ]);

        $this->assertSame('en_consulting', TerminologyLocale::resolve($company));
    }

    public function test_null_company_uses_default_profile_from_config(): void
    {
        config([
            'app.locale' => 'fr',
            'terminology.default_profile' => 'education',
        ]);

        $this->assertSame('fr', TerminologyLocale::resolve(null));
    }
}

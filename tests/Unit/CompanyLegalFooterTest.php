<?php

namespace Tests\Unit;

use App\Models\Company;
use Tests\TestCase;

class CompanyLegalFooterTest extends TestCase
{
    public function test_legal_footer_line_combines_company_fields(): void
    {
        app()->setLocale('fr');

        $company = new Company([
            'legal_form' => 'SASU',
            'share_capital' => '2 000',
            'siren' => '823059699',
        ]);

        $footer = $company->legalFooterLine();

        $this->assertNotNull($footer);
        $this->assertStringContainsString('SASU', $footer);
        $this->assertStringContainsString('823059699', $footer);
    }

    public function test_legal_footer_line_is_null_when_empty(): void
    {
        $company = new Company;

        $this->assertNull($company->legalFooterLine());
    }
}

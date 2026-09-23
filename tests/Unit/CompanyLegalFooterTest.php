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
        $this->assertStringContainsString('Capital de 2 000 €', $footer);
        $this->assertSame(1, mb_substr_count($footer, '€'));
    }

    public function test_legal_footer_line_does_not_duplicate_euro_when_share_capital_includes_symbol(): void
    {
        app()->setLocale('fr');

        $company = new Company([
            'legal_form' => 'SASU',
            'share_capital' => '2 000 €',
            'siren' => '823059699',
        ]);

        $footer = $company->legalFooterLine();

        $this->assertNotNull($footer);
        $this->assertStringContainsString('Capital de 2 000 €', $footer);
        $this->assertSame(1, mb_substr_count($footer, '€'));
    }

    public function test_legal_footer_line_share_capital_formats_correctly_in_english(): void
    {
        app()->setLocale('en');

        $company = new Company([
            'share_capital' => '2,000 €',
        ]);

        $footer = $company->legalFooterLine();

        $this->assertStringContainsString('Share capital: 2,000 €', $footer);
        $this->assertSame(1, mb_substr_count($footer, '€'));
    }

    public function test_legal_footer_line_is_null_when_empty(): void
    {
        $company = new Company;

        $this->assertNull($company->legalFooterLine());
    }
}

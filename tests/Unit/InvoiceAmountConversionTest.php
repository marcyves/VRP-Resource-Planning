<?php

namespace Tests\Unit;

use App\Http\Utility\Tools;
use Tests\TestCase;

class InvoiceAmountConversionTest extends TestCase
{
    public function test_vat_rate_matches_company_default_twenty_percent(): void
    {
        $this->assertSame(1.2, Tools::VAT_MULTIPLIER);
        $this->assertSame(20.0, Tools::vatRatePercent());
        $this->assertSame('20%', Tools::vatRateLabel());
    }

    public function test_ttc_converts_to_ht_at_two_decimals(): void
    {
        $this->assertSame(100.0, Tools::amountHtFromTtc(120));
        $this->assertSame(100.0, Tools::amountHtFromTtc('120,00'));
        $this->assertSame(83.33, Tools::amountHtFromTtc(100));
    }

    public function test_ht_converts_to_ttc_at_two_decimals(): void
    {
        $this->assertSame(120.0, Tools::amountTtcFromHt(100));
        $this->assertSame(120.0, Tools::amountTtcFromHt('100,00'));
        $this->assertSame(100.0, Tools::amountTtcFromHt(83.33));
    }

    public function test_ttc_round_trips_through_ht_for_typical_amounts(): void
    {
        foreach ([100.0, 120.0, 240.0, 500.0, 87.6] as $ttc) {
            $ht = Tools::amountHtFromTtc($ttc);
            $this->assertSame(
                round($ttc, 2),
                Tools::amountTtcFromHt($ht),
                "TTC round-trip failed for {$ttc}"
            );
        }
    }

    public function test_form_prefers_positive_ht_as_source_of_truth(): void
    {
        $this->assertSame(100.0, Tools::invoiceHtFromForm(100, 999, 888));
        $this->assertSame(100.0, Tools::invoiceHtFromForm('100,00', 120, null));
    }

    public function test_form_converts_ttc_when_ht_is_empty_or_not_positive(): void
    {
        $this->assertSame(100.0, Tools::invoiceHtFromForm(null, 120, null));
        $this->assertSame(100.0, Tools::invoiceHtFromForm('', 120, null));
        $this->assertSame(100.0, Tools::invoiceHtFromForm(0, 120, null));
        $this->assertSame(100.0, Tools::invoiceHtFromForm(null, null, 120));
    }

    public function test_form_returns_zero_or_negative_so_callers_can_reject(): void
    {
        $this->assertSame(0.0, Tools::invoiceHtFromForm(null, null, null));
        $this->assertSame(0.0, Tools::invoiceHtFromForm(0, 0, 0));
        $this->assertSame(-10.0, Tools::invoiceHtFromForm(-10, null, null));
        $this->assertSame(-10.0, Tools::invoiceHtFromForm(null, -12, null));
    }
}

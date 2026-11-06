<?php

namespace Tests\Unit;

use App\Support\RuneIcons;
use Tests\TestCase;

class RuneIconsTest extends TestCase
{
    public function test_duotone_svg_uses_navy_css_variables_not_fixed_greys(): void
    {
        $svg = RuneIcons::svg('calendar-range', 24);

        $this->assertStringContainsString('class="rune-icon"', $svg);
        $this->assertStringContainsString('var(--rune-duotone-primary, #00236f)', $svg);
        $this->assertStringContainsString('var(--rune-duotone-secondary, #4059aa)', $svg);
        $this->assertStringNotContainsString('#A4A5A6', $svg);
        $this->assertStringNotContainsString('#DDDDDD', $svg);
        $this->assertStringContainsString('width="24"', $svg);
        $this->assertStringContainsString('M5 4H19C20.1046 4 21 4.89543 21 6V10H3V6', $svg);
        $this->assertStringNotContainsString('M8 2V6Z', $svg);
    }

    public function test_aliases_resolve_to_known_rune_ids(): void
    {
        $this->assertSame('layouts-grid-2x2', RuneIcons::resolve('grid'));
        $this->assertSame('legacy-school', RuneIcons::resolve('school'));
        $this->assertSame('schedule-calendar', RuneIcons::resolve('calendar-range'));
        $this->assertSame('schedule-calendar', RuneIcons::resolve('calendar'));
        $this->assertSame('tools-trash', RuneIcons::resolve('trash'));
        $this->assertSame('tools-pencil', RuneIcons::resolve('edit'));
        $this->assertSame('messaging-send', RuneIcons::resolve('e-invoice'));
        $this->assertSame('documents-import', RuneIcons::resolve('import'));
    }

    public function test_school_uses_pre_rune_graduation_cap(): void
    {
        $svg = RuneIcons::svg('school');

        $this->assertStringContainsString('M22 10v6M2 10l10-5 10 5-10 5z', $svg);
        $this->assertStringContainsString('M6 12v5c3 3 9 3 12 0v-5', $svg);
        $this->assertStringContainsString('stroke="currentColor"', $svg);
        $this->assertStringNotContainsString('documents-book-bookmark', $svg);
    }

    public function test_unknown_name_falls_back_to_dot(): void
    {
        $svg = RuneIcons::svg('not-a-real-icon');

        $this->assertNotSame('', $svg);
        $this->assertStringContainsString('rune-icon', $svg);
    }

    public function test_repeated_icons_do_not_share_clip_ids(): void
    {
        $first = RuneIcons::svg('layers');
        $second = RuneIcons::svg('layers');

        preg_match_all('/id="([^"]+)"/', $first, $a);
        preg_match_all('/id="([^"]+)"/', $second, $b);

        if ($a[1] === [] && $b[1] === []) {
            $this->assertTrue(true);

            return;
        }

        $this->assertNotEquals($a[1], $b[1]);
    }
}

<?php

namespace Tests\Unit;

use App\Support\UserAgentParser;
use Tests\TestCase;

class UserAgentParserTest extends TestCase
{
    private UserAgentParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new UserAgentParser;
    }

    public function test_parses_iphone_safari(): void
    {
        $parsed = $this->parser->parse(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
        );

        $this->assertSame('iOS', $parsed['os']);
        $this->assertSame('mobile', $parsed['device_type']);
        $this->assertSame('Safari', $parsed['browser']);
    }

    public function test_parses_ipad_as_tablet(): void
    {
        $parsed = $this->parser->parse(
            'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
        );

        $this->assertSame('iOS', $parsed['os']);
        $this->assertSame('tablet', $parsed['device_type']);
        $this->assertSame('Safari', $parsed['browser']);
    }

    public function test_parses_android_chrome_mobile(): void
    {
        $parsed = $this->parser->parse(
            'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36'
        );

        $this->assertSame('Android', $parsed['os']);
        $this->assertSame('mobile', $parsed['device_type']);
        $this->assertSame('Chrome', $parsed['browser']);
    }

    public function test_parses_android_tablet_without_mobile_token(): void
    {
        $parsed = $this->parser->parse(
            'Mozilla/5.0 (Linux; Android 12; SM-T870) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        $this->assertSame('Android', $parsed['os']);
        $this->assertSame('tablet', $parsed['device_type']);
        $this->assertSame('Chrome', $parsed['browser']);
    }

    public function test_parses_windows_edge_before_chrome(): void
    {
        $parsed = $this->parser->parse(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0'
        );

        $this->assertSame('Windows', $parsed['os']);
        $this->assertSame('desktop', $parsed['device_type']);
        $this->assertSame('Edge', $parsed['browser']);
    }

    public function test_parses_macos_firefox(): void
    {
        $parsed = $this->parser->parse(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0'
        );

        $this->assertSame('macOS', $parsed['os']);
        $this->assertSame('desktop', $parsed['device_type']);
        $this->assertSame('Firefox', $parsed['browser']);
    }

    public function test_empty_user_agent_is_bot(): void
    {
        $this->assertTrue($this->parser->isBot(null));
        $this->assertTrue($this->parser->isBot(''));
        $this->assertTrue($this->parser->isBot('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'));
        $this->assertFalse($this->parser->isBot('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36'));
    }
}

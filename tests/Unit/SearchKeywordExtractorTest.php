<?php

namespace Tests\Unit;

use App\Support\SearchKeywordExtractor;
use Tests\TestCase;

class SearchKeywordExtractorTest extends TestCase
{
    private SearchKeywordExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = new SearchKeywordExtractor;
    }

    public function test_reads_q_from_landing_query(): void
    {
        $this->assertSame('vrp plan', $this->extractor->fromLandingOrReferer(
            ['q' => 'vrp plan', 'utm_source' => 'google'],
            'https://www.google.com/search?q=other'
        ));
    }

    public function test_falls_back_to_referer_query_keys(): void
    {
        $this->assertSame(
            'formation freelance',
            $this->extractor->fromReferer('https://www.bing.com/search?q=formation+freelance')
        );
        $this->assertSame(
            'agenda',
            $this->extractor->fromReferer('https://search.yahoo.com/search?p=agenda')
        );
        $this->assertSame(
            'invoice software',
            $this->extractor->fromLandingOrReferer([], 'https://example.test/s?query=invoice+software')
        );
        $this->assertSame(
            'keyword from text',
            $this->extractor->fromLandingOrReferer(['text' => 'keyword from text'], null)
        );
        $this->assertSame(
            'term',
            $this->extractor->fromLandingOrReferer(['utm_term' => 'term'], null)
        );
    }

    public function test_utm_prefers_landing_over_referer(): void
    {
        $utm = $this->extractor->utmFromLandingOrReferer(
            ['utm_source' => 'newsletter', 'utm_medium' => 'email'],
            'https://google.com/?utm_source=google&utm_campaign=brand'
        );

        $this->assertSame('newsletter', $utm['utm_source']);
        $this->assertSame('email', $utm['utm_medium']);
        $this->assertSame('brand', $utm['utm_campaign']);
        $this->assertNull($utm['utm_content']);
        $this->assertNull($utm['utm_term']);
    }

    public function test_host_from_referer(): void
    {
        $this->assertSame('www.google.com', $this->extractor->hostFromUrl('https://www.google.com/search?q=vrp'));
        $this->assertNull($this->extractor->hostFromUrl(null));
        $this->assertNull($this->extractor->hostFromUrl(''));
    }
}

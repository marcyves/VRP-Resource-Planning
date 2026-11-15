<?php

namespace App\Support;

final class SearchKeywordExtractor
{
    /** @var list<string> */
    public const KEYS = ['q', 'query', 'p', 'text', 'keyword', 'utm_term'];

    /** @var list<string> */
    public const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    /**
     * @param  array<string, mixed>  $query
     */
    public function fromQueryArray(array $query): ?string
    {
        foreach (self::KEYS as $key) {
            $value = $query[$key] ?? null;

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value !== '') {
                return mb_substr($value, 0, 255);
            }
        }

        return null;
    }

    public function fromReferer(?string $referer): ?string
    {
        $params = $this->queryFromUrl($referer);

        return $params === [] ? null : $this->fromQueryArray($params);
    }

    /**
     * Prefer the landing URL, then the referer.
     *
     * @param  array<string, mixed>  $query
     */
    public function fromLandingOrReferer(array $query, ?string $referer): ?string
    {
        return $this->fromQueryArray($query) ?? $this->fromReferer($referer);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{utm_source: ?string, utm_medium: ?string, utm_campaign: ?string, utm_content: ?string, utm_term: ?string}
     */
    public function utmFromLandingOrReferer(array $query, ?string $referer): array
    {
        $refererQuery = $this->queryFromUrl($referer);
        $utm = [];

        foreach (self::UTM_KEYS as $key) {
            $utm[$key] = $this->stringParam($query[$key] ?? null)
                ?? $this->stringParam($refererQuery[$key] ?? null);
        }

        return $utm;
    }

    public function hostFromUrl(?string $url): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        return mb_substr(strtolower($host), 0, 255);
    }

    /**
     * @return array<string, mixed>
     */
    public function queryFromUrl(?string $url): array
    {
        if (! is_string($url) || trim($url) === '') {
            return [];
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return [];
        }

        $params = [];
        parse_str($query, $params);

        return is_array($params) ? $params : [];
    }

    private function stringParam(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 255);
    }
}

<?php

namespace App\Support;

final class UserAgentParser
{
    /**
     * @return array{os: string, device_type: string, browser: string}
     */
    public function parse(?string $userAgent): array
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return [
                'os' => 'unknown',
                'device_type' => 'desktop',
                'browser' => 'unknown',
            ];
        }

        return [
            'os' => $this->os($ua),
            'device_type' => $this->deviceType($ua),
            'browser' => $this->browser($ua),
        ];
    }

    public function isBot(?string $userAgent): bool
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawler|spider|crawl|slurp|preview|monitor|lighthouse|headless|wget|curl|python-requests|php\/|scrapy|httpclient|go-http-client/i',
            $ua
        );
    }

    private function os(string $ua): string
    {
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            return 'iOS';
        }

        if (stripos($ua, 'Android') !== false) {
            return 'Android';
        }

        if (stripos($ua, 'Windows') !== false) {
            return 'Windows';
        }

        if (stripos($ua, 'CrOS') !== false) {
            return 'ChromeOS';
        }

        if (preg_match('/Mac OS X|Macintosh/i', $ua)) {
            return 'macOS';
        }

        if (stripos($ua, 'Linux') !== false) {
            return 'Linux';
        }

        return 'unknown';
    }

    private function deviceType(string $ua): string
    {
        if (preg_match('/iPad|Tablet|PlayBook/i', $ua) || (stripos($ua, 'Android') !== false && stripos($ua, 'Mobile') === false)) {
            return 'tablet';
        }

        if (preg_match('/iPhone|iPod|Mobile|Mobi|Android/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function browser(string $ua): string
    {
        if (stripos($ua, 'Edg/') !== false || stripos($ua, 'Edge/') !== false) {
            return 'Edge';
        }

        if (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
            return 'Opera';
        }

        if (stripos($ua, 'SamsungBrowser') !== false) {
            return 'Samsung Internet';
        }

        if (stripos($ua, 'Firefox/') !== false || stripos($ua, 'FxiOS') !== false) {
            return 'Firefox';
        }

        if (stripos($ua, 'Chrome/') !== false || stripos($ua, 'CriOS') !== false) {
            return 'Chrome';
        }

        if (stripos($ua, 'Safari/') !== false) {
            return 'Safari';
        }

        if (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident/') !== false) {
            return 'Internet Explorer';
        }

        return 'unknown';
    }
}

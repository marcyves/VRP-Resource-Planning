<?php

namespace App\Support;

/**
 * Renders committed Rune Icons duotone SVGs (navy-recolored) as inline markup.
 */
final class RuneIcons
{
    public static function resolve(string $name): string
    {
        return RuneIconCatalog::ALIASES[$name] ?? $name;
    }

    public static function svg(string $name, ?int $size = null, string $class = ''): string
    {
        $id = self::resolve($name);
        $markup = RuneIconCatalog::MARKUP[$id] ?? null;

        if ($markup === null) {
            $id = RuneIconCatalog::ALIASES['dot'] ?? 'indicators-circle-dot';
            $markup = RuneIconCatalog::MARKUP[$id] ?? '';
        }

        if ($markup === '') {
            return '';
        }

        $markup = self::uniquifyIds($markup);
        $classes = trim('rune-icon '.$class);

        if ($classes !== '') {
            $markup = preg_replace(
                '/<svg\b/',
                '<svg class="'.e($classes).'"',
                $markup,
                1,
            ) ?? $markup;
        }

        if ($size !== null) {
            $size = max(1, $size);
            $markup = preg_replace('/\bwidth="\d+"/', 'width="'.$size.'"', $markup, 1) ?? $markup;
            $markup = preg_replace('/\bheight="\d+"/', 'height="'.$size.'"', $markup, 1) ?? $markup;
        }

        return $markup;
    }

    private static function uniquifyIds(string $markup): string
    {
        if (! str_contains($markup, 'id="')) {
            return $markup;
        }

        $uid = 'ri'.bin2hex(random_bytes(4));
        $ids = [];

        $markup = preg_replace_callback('/\bid="([^"]+)"/', function (array $match) use ($uid, &$ids): string {
            $ids[$match[1]] = $match[1].'-'.$uid;

            return 'id="'.$ids[$match[1]].'"';
        }, $markup) ?? $markup;

        foreach ($ids as $old => $new) {
            $markup = str_replace(
                ['url(#'.$old.')', 'href="#'.$old.'"'],
                ['url(#'.$new.')', 'href="#'.$new.'"'],
                $markup,
            );
        }

        return $markup;
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * The languages offered on the shop and customer pages (config/shop.php),
 * and the translated wording for the browser.
 */
class Locales
{
    /** @var array<string, array<string, string>> */
    private static array $loaded = [];

    /** @return array<string, string> code => name in its own language */
    public static function all(): array
    {
        /** @var array<string, string> */
        return config('shop.locales', ['en' => 'English']);
    }

    public static function supports(string $locale): bool
    {
        return array_key_exists($locale, self::all());
    }

    /**
     * English wording => translation, for the React pages. Empty for English,
     * which is the source text.
     *
     * @return array<string, string>
     */
    public static function translations(string $locale): array
    {
        if ($locale === 'en' || ! self::supports($locale)) {
            return [];
        }

        if (! isset(self::$loaded[$locale])) {
            $path = lang_path("{$locale}.json");

            /** @var array<string, string> $lines */
            $lines = File::exists($path) ? json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR) : [];

            self::$loaded[$locale] = $lines;
        }

        return self::$loaded[$locale];
    }
}

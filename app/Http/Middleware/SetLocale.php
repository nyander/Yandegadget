<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uses the language the visitor picked in the switcher (a cookie), if it's
 * one the shop offers. Everyone else gets the default from config/app.php.
 * The admin panel (and its Livewire requests) always stays in English.
 */
class SetLocale
{
    public const COOKIE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        if (self::isAdmin($request)) {
            return $next($request);
        }

        $locale = $request->cookie(self::COOKIE);

        if (is_string($locale) && Locales::supports($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    public static function isAdmin(Request $request): bool
    {
        return $request->is('admin', 'admin/*') || $request->hasHeader('X-Livewire');
    }
}

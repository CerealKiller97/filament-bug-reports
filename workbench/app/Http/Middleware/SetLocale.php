<?php

namespace Workbench\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dev-only locale switcher for the served workbench panel.
 *
 * Append `?locale=sr` (or `en`) to any URL to switch, e.g.
 * http://127.0.0.1:8000/admin?locale=sr — the choice sticks in the session so
 * every subsequent Filament request renders in that language.
 */
class SetLocale
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'sr', 'de'];

    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('locale');

        if (is_string($requested) && in_array($requested, self::SUPPORTED, true)) {
            $request->session()->put('locale', $requested);
        }

        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

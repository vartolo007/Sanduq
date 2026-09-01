<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Applies the session locale on every request so RTL/LTR follows the user's
 * choice. Register it in bootstrap/app.php (Laravel 11+) or Kernel.php (10 and
 * earlier) inside the 'web' middleware group, before the route is handled.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'ar'));

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        // Carbon dates (used by ->translatedFormat) follow the same locale.
        \Carbon\Carbon::setLocale($locale);

        return $next($request);
    }
}

<?php

namespace App\Domain\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class Localization
{

    private $skippedPrefixes = ['api', 'admin', 'captcha'];

    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1); // Get the first segment of the URL

        if ($locale === App::getLocale() || in_array($locale, $this->skippedPrefixes)) {
            return $next($request); // If the locale is the same as the app locale, don't do anything
        }

        if (array_key_exists($locale, Config::get('languages'))) {
            App::setLocale($locale);
            \Log::debug('Locale set from URL prefix: ' . $locale);
            Session::put('applocale', $locale); // Store the locale in session
        } elseif (Session::has('applocale') && array_key_exists(Session::get('applocale'), Config::get('languages'))) {
            $locale = Session::get('applocale');
            App::setLocale($locale);
            \Log::debug('Locale set from session: ' . $locale);
        } else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            $fallbackLocale = Config::get('app.fallback_locale');
            App::setLocale($fallbackLocale);
            \Log::debug('Locale set to fallback: ' . $fallbackLocale);
        }

        // Redirect to the URL with the locale prefix if it's not already present
        if (!array_key_exists($locale, Config::get('languages'))) {
            $locale = App::getLocale();
            \Log::debug('Locale not found in languages config, using app locale: ' . $locale);
            $segments = $request->segments();
            array_unshift($segments, $locale);
            \Log::debug('Redirecting to URL with locale prefix: ' . implode('/', $segments));
            return redirect()->to(implode('/', $segments));
        }
        return $next($request);
    }
}


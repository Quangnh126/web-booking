<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //Check header request and set language defaut
        $lang = ($request->hasHeader('language')) ? $request->header('language') : 'vi';
        //Set laravel localization
        app()->setLocale($lang);

        //Continue request
        return $next($request);
    }
}

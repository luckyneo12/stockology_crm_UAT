<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Populate PHP native session with Laravel session for standalone scripts
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (auth()->check()) {
            $_SESSION['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'] = auth()->id();
        } else {
            unset($_SESSION['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d']);
        }

        if(file_exists(storage_path() . "/installed"))
        {
            \App::setLocale(getActiveLanguage());
        }

        // $input = $request->all();

        // array_walk_recursive($input, function(&$input) {
        //     $input = strip_tags($input);
        // });

        // $request->merge($input);

        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars_decode($value);
                $value = preg_replace('/<\s*script\b[^>]*>(.*?)<\s*\/\s*script\s*>/is', '', $value);
                $value = str_replace(['&lt;', '&gt;', 'javascript','alert'], '', $value);
            }
        });
        $request->merge($input);

        return $next($request);
    }
}

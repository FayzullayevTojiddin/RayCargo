<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLangMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->lang) {
            App::setlocale($request->user()->lang);
        }

        else {
            App::setlocale(
                $request->header('Accept-Language', config('app.locale'))
            );
        }

        return $next($request);
    }
}

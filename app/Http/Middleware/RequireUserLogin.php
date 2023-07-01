<?php

namespace App\Http\Middleware;

use \App\Session\User\Login as SessionUserLogin;

class RequireUserLogin
{

    public function handle($request, $next)
    {
        if (SessionUserLogin::isLogged()) {
            return $next($request);
        }

        $request->getRouter()->redirect('/login');
    }
}

<?php

namespace App\Http\Middleware;

use \App\Session\User\Login as SessionUserLogin;

class RequireAdminLogin
{
    private static function init()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function handle($request, $next)
    {
        self::init();

        if (SessionUserLogin::isLogged() && isset($_SESSION['user']['admin']['id'])) {
            return $next($request);
        }
        if (isset($_SESSION['user']['operator']['id']))
            $request->getRouter()->redirect('/dashboard');
        else
            $request->getRouter()->redirect('/');
    }
}

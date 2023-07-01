<?php

namespace App\Controller;

use \League\Plates\Engine;
use \App\Http\Request;
use \App\Http\Router;

class Controller
{

    protected static $view;
    private static $route;

    public function init()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
    public function __construct($dir = null, $data = [])
    {
        self::$view = new Engine(__DIR__ . '/../../resources/view', 'php');
        self::$view->addData($data);
    }

    public static function back()
    {
        self::$route = true;

        return new Controller;
    }

    public function with($params = [])
    {
        $this->init();
        foreach ($params as $key => $value)
            $_SESSION['usererrormessages'][$key] = $value;
    }

    public function __destruct()
    {
        $request = new Request(new Router(URL));
        $this->init();
        if (self::$route) {
            $request->getRouter()->redirect(explode(URL,$_SERVER['HTTP_REFERER'])[1]);
        }
    }
}

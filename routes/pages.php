<?php

use App\Controller\Pages;
use App\Http\Response;

new Pages\ControladorSorteiro();

$obRouter->get('/', [
    'middlewares' => [
        'require-user-login'
    ],
    function () {
        return new Response(200, Pages\ControladorSorteiro::getLayoutPage());
    }
]);

$obRouter->post('/', [
    'middlewares' => [
        'require-user-login'
    ],
    function ($request) {
        return new Response(200, Pages\ControladorSorteiro::store($request));
    }
]);

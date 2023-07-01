<?php

namespace App\Controller\Api;

class Api
{

    public static function getDetails()
    {
        return [
            'nome' => 'API > TechMaster',
            'versao' => 'v1.0.0',
            'autor' => 'Luis Ferreira',
            'email' => ''
        ];
    }

    protected static function getPagination($request, $obPagination)
    {
        $queryParams = $request->getQueryParams();

        $pages = $obPagination->getPages();

        return [
            "paginaActual" => isset($queryParams['page']) ? (int)$queryParams['page'] : 1,
            "quantidadePaginas" => !empty($pages) ? count($pages) : 1
        ];
    }

}
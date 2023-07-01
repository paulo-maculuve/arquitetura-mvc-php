<?php

  namespace App\Helper\File;

  class CSV {

    public static function readFile($arquivo, $cabecalho = true, $delimitador = ',') {
      // VERIFICA SE O ARQUIVO EXISTE
      if (!file_exists($arquivo) ) return 'arquivo nao encontrado';

      $dados = [];

      $csv = fopen($arquivo, 'r');

      // CABECALHO DE DADOS
      $cabecalhoDados = $cabecalho ? fgetcsv($csv, 0, $delimitador) : [];

      // ITERA O ARQUIVO, LENDO LINHA A LINHA
      while($linha = fgetcsv($csv, 0, $delimitador)) $dados[] = $cabecalho ? array_combine($cabecalhoDados, $linha) : $linha;

      // FECHA O ARQUIVO CSV
      fclose($csv);

      // RETORNA OS DADOS PROCESSADOS
      return $dados;
      
    }

    public static function createFile($arquivo, $dados, $delimitador = ',') {

      $csv = fopen($arquivo, 'w');

      foreach ($dados as $linha) {
        fputcsv($csv, $linha, $delimitador);
      }

      fclose($csv);

      // RETORNA SUCESSO
      return true;
    }
  } 
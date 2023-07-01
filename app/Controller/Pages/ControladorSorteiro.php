<?php

  namespace App\Controller\Pages;

  use App\Controller\Controller; 
  use \App\Helper\File\CSV;
  use \App\Helper\File\Upload;
  use \App\Model\Entity\Dado;
  use \App\Model\Entity\Winner;
  use \App\Model\Entity\AllWinner;

  class ControladorSorteiro extends Controller {

    public static function getLayoutPage() {
    
      return self::$view->render('pages/random-nestle/index', [

      ]);
    }

    public static function store( $request ) {

      $request->validate([
        'arquivo' => ['mimes:csv,txt']
      ]);

      $path = __DIR__.'/../../../storage';
      $upload = new Upload($_FILES['arquivo']);
      $upload->setName('numeros-dos-participantes');
      $upload->Upload($path);

      $dados = CSV::readfile($path.'/'.$upload->getBasename(), true, ',');
      (new Dado())->delete();

      foreach ($dados as $dado) {
        if (empty($dado['Msisdn'])) break; 
        $obDados = new Dado;
        $obDados->data = $dado['Date'];
        $obDados->nome = $dado['Name'];
        $obDados->msisdn = $dado['Msisdn'];
        $obDados->provincia = $dado['Province'];
        $obDados->store = $dado['Store'];        
        $obDados->create();
      }

      // Fazer upload do arquivo .csv para tabela 'allwinner'
      // $allWinners = CSV::readfile($path.'/'.$upload->getBasename(), true, ',');
      // (new AllWinner())->delete();

      // foreach ($allWinners as $Winner) {
      //   if (empty($Winner['Msisdn'])) break; 
      //   $obWinner = new AllWinner;
      //   $obWinner->data = $Winner['Date'];
      //   $obWinner->nome = $Winner['Name'];
      //   $obWinner->msisdn = $Winner['Msisdn'];
      //   $obWinner->provincia = $Winner['Province'];
      //   $obWinner->store = $Winner['Store'];        
      //   $obWinner->create();
      // }
       
      return $request->getRouter()->redirect('/sorteio');
    }

   

}
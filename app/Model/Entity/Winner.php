<?php


namespace App\Model\Entity;


use App\Db\Database;

class Winner extends Database {
  
  public $id;
  public $data;
  public $nome;
  public $msisdn;
  public $provincia;
  public $store;

  public function create() {

    $this->id = (new Database('winners'))->insert([
      'data'      => $this->data,
      'nome'      => $this->nome,
      'msisdn'    => $this->msisdn,
      'provincia' => $this->provincia,
      'store'     => $this->store,
    ]);

    return true;
  }
}
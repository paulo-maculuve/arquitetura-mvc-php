<?php


namespace App\Model\Entity;


use App\Db\Database;

class User extends Database {
  
  public $id;
  public $nome;
  public $email;
  public $senha;

  public function create() {

    $this->id = (new Database('users'))->insert([
      'nome'      => $this->nome,
      'email'      => $this->email,
      'senha'    => $this->senha,
    ]);

    return true;
  }
}
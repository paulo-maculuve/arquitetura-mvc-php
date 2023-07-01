<?php

    namespace App\Helper;

    use \App\Db\Database;
    use \App\Http\Request;
    use \App\Http\Router;

    class Validate {

        private $postVars;
        private $validators = [];
        private $messages = [];

        private function init() {
            if (session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }
        }

        public function __construct($validators, $postVars) {
            $this->postVars   = $postVars;
            $this->validators = $validators;
        }

        public function confirmed($field) {

            if ($this->postVars[$field] === $this->postVars['password_confirmation']) return true;
            $this->messages[$field] = 'unconfirmed password';
            return false;
        }

        public function number($field) {
            if (empty($this->postVars[$field])) return true;
            if (is_numeric($this->postVars[$field])) return true;

            $this->messages[$field] = 'the field '.str_replace('_', ' ', $field).' must to be type of number';
            return false;
        }

        public function required($field) {
            
            if (!empty($_FILES[$field]['name'])) return true;

            $result = ltrim(rtrim($this->postVars[$field] ?? ''));

            if (empty($result)) {
                $this->messages[$field] = 'the field '.str_replace('_', ' ', $field).' is required';
                return false;
            } 

            return true;
        }

        public function unique($field, $table) {
            $obDatabase = new Database($table);
            $data = $obDatabase->where($field, $this->postVars[$field])->first();

            if (!empty($data)) {
                $this->messages[$field] = 'the '.str_replace('_', ' ', $field).' '.$this->postVars[$field].' has been taken';
                return false;
            }

            return true;
        }

        public function mimes($field, $allowedExtensions) {

            if (empty($_FILES[$field]['name'])) return true;

            $source = $_FILES[$field]['name'];
            $pattern = '/['.$allowedExtensions.','.strtoupper($allowedExtensions).']$/';
            if (preg_match($pattern, $source)) return true;
            $this->messages[$field] = 'the field '.str_replace('_', ' ', $field).' must to be type of '.$allowedExtensions;
            return false;
        }

        public function max($field, $qtd) {
            $value = $this->postVars[$field];

            if (strlen($value) > $qtd) {
                $this->messages[$field] = 'the field '.str_replace('_', ' ', $field).' must contain a maximum of '.$qtd.' characters';
                return false;
            }

            return true;
        }

        public function min($field, $qtd) {
            $value = $this->postVars[$field];

            if (strlen($value) < $qtd) {
                $this->messages[$field] = 'the field '.str_replace('_', ' ', $field).' must contain a minimum of '.$qtd.' characters';
                return false;
            }

            return true;
        }


        public function verifyIfExistIndex($field) {
            $keys = array_keys($this->postVars);
            if (in_array($field, $keys)) return true;
            return false;
        }

        public function email($field) {
            if (empty($this->postVars[$field])) return true;
            if (!filter_var($this->postVars[$field], FILTER_VALIDATE_EMAIL)) {
                $this->messages[$field] = 'the field '.str_replace('_', ' ', $field).' must to be email';
                return false;
            }
        }

        public function makeValidation($field, $validations) {

            foreach ($validations as $validation) {
                if ($this->verifyIfExistIndex($field) || isset($_FILES[$field])) {
                    $params = explode(':', $validation);
                    if (call_user_func_array([$this, $params[0]], [$field, $params[1] ?? null])) continue;
                }
            }
            if (!empty($this->messages)) return false;
            return true;
        }

        public function make() {

            $router = new Router(URL);
            $request = new Request($router);
            $this->init();

            foreach ($this->validators as $field => $validations) $this->makeValidation($field, $validations);
            if (!empty($this->messages)) {
                $_SESSION['usererrormessages']['errormessages'] = $this->messages;
                
                return $request->getRouter()->redirect(explode(URL,$_SERVER['HTTP_REFERER'])[1]);
            } 
            return true;
        }

        public function getMessages() {
            return $this->messages;
        }
    }
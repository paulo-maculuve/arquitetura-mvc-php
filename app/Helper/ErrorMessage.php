<?php

    namespace App\Helper;

    class ErrorMessage{

        private $globals;

        public static function init() {
            if (session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }
        }

        public function __construct()
        {
            $this->globals = $GLOBALS;
        }

        public static function get($position = 'errormessages') {
            self::init();

            $tmp = !empty($_SESSION['usererrormessages'][$position])  ? $_SESSION['usererrormessages'][$position] : '';
            $_SESSION['usererrormessages'][$position] = '';
            return $tmp;
        }
    }
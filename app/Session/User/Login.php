<?php

	namespace App\Session\User;

	use \App\Model\Entity\User;

	Class Login {
		private static function init(){
			if (session_status() != PHP_SESSION_ACTIVE) {
				session_start();
			}
		}

		public static function login($obUser){
			self::init();
	
			$_SESSION['user'] = [
				'id' => $obUser->id,
				'username'=> $obUser->nome,
				'email' => $obUser->email
			];

			return true;
		}
		public static function isLogged(){
			self::init();
			 
			if(isset($_SESSION['user']['id'])){
				return isset($_SESSION['user']['id']);
			}

			return false;
		}
		public static function logout(){
			self::init();

			if(isset($_SESSION['user']['id'])){
				unset($_SESSION['user']);
			}
				
			return true;
		}
	}

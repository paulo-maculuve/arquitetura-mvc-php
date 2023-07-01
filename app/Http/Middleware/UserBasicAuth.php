<?php

	namespace App\Http\Middleware;

    use \Exception;
    use \App\Model\Entity\User;

	Class UserBasicAuth {

        private function getBasicAuthUser(){
            
            if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) return false;

            $obUser = User::getUserByEmail($_SERVER['PHP_AUTH_USER']);
            if(!$obUser instanceof User) return false;

            return password_verify($_SERVER['PHP_AUTH_PW'], $obUser->password) ? $obUser : false;
        }

        /**
         * Metodo responsavel por validar o acesso via basic auth
         * @param $request
         */
        private function basicAuth($request){
            
            if($obUser = $this->getBasicAuthUser()){
                $request->user = $obUser;
                return true;
            }

            throw new Exception("Usuario ou senha inválido", 403);
            
        }

		public function handle($request, $next){
            $this->basicAuth($request);
            
			return $next($request);
		}

	}
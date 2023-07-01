<?php

	namespace App\Http\Middleware;

	Class Api {

		public function handle($request, $next){
			
            //ALTERA O CONTENT TYPE PA JSON
            $request->getRouter()->setContentType('application/json');
            
			return $next($request);
		}

	}
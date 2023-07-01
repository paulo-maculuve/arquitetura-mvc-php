<?php

	namespace App\Http\Middleware;

	Class Maintenance {

		public function handle($request, $next){
			if (getenv('MAINTENANCE') == 'true') {
				throw new \Exception("Pagina em manutenção, Tente novamente mais tarde", 200);
			}
			return $next($request);
		}

	}
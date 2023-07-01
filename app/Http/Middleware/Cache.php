<?php

	namespace App\Http\Middleware;

    use \App\Utils\Cache\File as CacheFile;

	Class Cache {

        /**
         * METDODO RESPONSAVEL POR VERIFICAR SE A REQUEST E CACHEAVEL
         * @param Request request
         * @return boolean
         */
        private function isCacheable($request){
            //VALIDA O TEMPO DE CACHE
            if(getenv('CACHE_TIME') <= 0) return false;
            
            //VALIDA O METODO DA REQUISICAO
            if($request->getHttpMethod() != 'GET') return false;

            $headers = $request->getHeaders();
            if(isset($headers['Cache-Control']) && $headers['Cache-Control'] == 'no cache') return false;
            //CACHEAVEL
            return true;
        }

        /**
         * Metdo responsavel por retornar a hash do cache
         * @param Request $request
         * @return string
         */
        private function getHash($request){
            //URI DA ROTA
            $uri = $request->getRouter()->getUri();

            //QUERY PARAMS 
            $queryParams = $request->getQueryParams();
            $uri .= !empty($queryParams) ? '?'.http_build_query($queryParams) : '';

            return rtrim('route-'.preg_replace('/[^0-9a-zA-Z]/', '-', ltrim($uri,'/')),'-');
        }

		public function handle($request, $next){
			//VERIFICA SE A REQUEST E CACHEAVEL
			if(!$this->isCacheable($request)) return $next($request);

            //HASH DO CACHE
            $hash = $this->getHash($request);

            //RETORNA OS DADOS DO CACHE
            return CacheFile::getCache($hash, getenv('CACHE_TIME'), function() use ($request, $next){
                return $next($request);
            });
		}

	}
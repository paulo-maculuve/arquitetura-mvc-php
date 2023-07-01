<?php

	namespace App\Http;

	use \Closure;
	use \Exception;
	use \ReflectionFunction;
	use \App\Http\Middleware\Queue as MiddlewareQueue;

	Class Router {

		private $url = '';
		private $prefix = '';
		private $routes = [];
		private $request;
		private $contentType = 'text/html';

		public function __construct($url){
			$this->request = new Request($this);
			$this->url = $url;
			$this->setPrefix();
		}
		/**
		 * Metodo responsavelpor alterar o valor do content type
		 * @param string $contentType
		 */
		public function setContentType($contentType){
			$this->contentType = $contentType;
		}

		private function setPrefix(){
			$parseUrl = parse_url($this->url);
			$parseUrl['path'] = $parseUrl['path'] ?? getenv('PATH');
			$this->prefix = $parseUrl['path'];
		}
		
		private function addRoute($method, $route, $params = []){
			foreach ($params as $key => $value) {
				if ($value instanceof Closure) {
					$params['controller'] = $value;
					unset($params[$key]);
					continue;
				}
			}
			//MIDDLEWARE DA ROTA
			$params['middlewares'] = $params['middlewares'] ?? [];

			$params['variables'] = [];

			$patternVariable = '/{(.*?)}/';
			if (preg_match_all($patternVariable, $route, $matches)) {
				$route = preg_replace($patternVariable, '(.*?)', $route);
				$params['variables'] = $matches[1];
			}
			//REMOVE BARA NO FINAL DA ROTA
			$route = rtrim($route, '/');
			$patternRoute = '/^'.str_replace('/', '\/', $route).'$/';
			$this->routes[$patternRoute][$method] = $params;
		}
			
		public function get($route, $params = []){
			return $this->addRoute('GET', $route, $params);
		}
		public function post($route, $params = []){
			return $this->addRoute('POST', $route, $params);
		}
		public function put($route, $params = []){
			return $this->addRoute('PUT', $route, $params);
		}
		public function delete($route, $params = []){
			return $this->addRoute('DELETE', $route, $params);
		}
		public function getUri(){
			$uri = $this->request->getUri();

			$xUri = strlen($this->prefix) ? explode($this->prefix, $uri) : [$uri];

			return rtrim(end($xUri), '/');
		}
		private function getRoute(){
			$uri = $this->getUri();

			$httpMethod = $this->request->getHttpMethod();
			foreach ($this->routes as $patternRoute => $methods) {
				if (preg_match($patternRoute, $uri, $matches)) {
					if (isset($methods[$httpMethod])) {
						unset($matches[0]);

						$keys = $methods[$httpMethod]['variables'];
						$methods[$httpMethod]['variables'] = array_combine($keys, $matches);
						$methods[$httpMethod]['variables']['request'] = $this->request;

						return $methods[$httpMethod];
					}
					throw new Exception("Metodo não permitido", 405);
				}
				
			}
			throw new Exception("URL não encontrada", 404);

		}
		
		public function run(){
			try {

				$route = $this->getRoute();
				
				if (!isset($route['controller'])) {
					//self::log()->warning("  problemas no processamento da url  /// DATA: ".date('Y/m/d H:i:s').", URI: ".$_SERVER['REQUEST_URI'].", HOST: ".$_SERVER['HTTP_HOST'].", METHOD: ".$_SERVER['REQUEST_METHOD'].", USER_AGENT: ".$_SERVER['HTTP_USER_AGENT'], ["logger" => true]);
					throw new Exception("A URL não pode ser processada", 500);
				}

				$args = [];

				$reflection = new ReflectionFunction($route['controller']);
				foreach ($reflection->getParameters() as $parameter) {
					$name = $parameter->getName();
					$args[$name] = $route['variables'][$name] ?? '';
				}

				
				return (new MiddlewareQueue($route['middlewares'],$route['controller'],$args))->next($this->request);
			}catch (Exception $e) {
				return new Response($e->getCode(),$this->getErrorMessage($e->getMessage()), $this->contentType);
			}
		}

		private function getErrorMessage($message){
			switch ($this->contentType) {
				case 'application/json':
					return [
						'error' => $message
					];
					break;
				
				default:
					return $message;
					break;
			}
		}

		public function redirect($route){
			$url = $this->url.$route;

			header('location: '.$url);
			exit;
		}
	}
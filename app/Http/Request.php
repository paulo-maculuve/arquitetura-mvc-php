<?php

	namespace App\Http;

	use \App\Helper\Validate;

	Class Request {

		private $router;
		private $httpMethod;
		private $uri;
		private $queryParams = [];
		private $postVars = [];
		private $headers = [];
		private $errorMessages;

		public function __construct($router){
			$this->router = $router;
			$this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
			$this->queryParams = $_GET ?? [];
			$this->headers = getallheaders();
			$this->setUri();
			$this->setPostVars();
		}

		private function setPostVars(){
			if($this->httpMethod == 'GET') return false;

			$this->postVars = $_POST ?? [];

			$inputRaw = file_get_contents('php://input');
			$this->postVars = (strlen($inputRaw) && empty($_POST)) ? json_decode($inputRaw, true) : $this->postVars;
		}
		public function setUri(){
			$this->uri = $_SERVER['REQUEST_URI'] ?? '';

			$xUri = explode('?', $this->uri);
			$this->uri = $xUri[0];
		}
		public function getRouter(){
			return $this->router;
		}
		public function getHttpMethod(){
			return $this->httpMethod;
		}
		public function getUri(){
			return $this->uri;
		}
		public function getQueryParams(){
			return $this->queryParams;
		}
		public function getPostVars(){
			return $this->postVars;
		}
		public function getHeaders(){
			return $this->headers;
		}

		public function validate($fields = []) {
			// INICIAR O CONSTRUTOR DA CLASSE VALIDATE
			$validation = new Validate($fields, $this->postVars);

			if (!$validation->make()) {
				$this->errorMessages = $validation->getMessages();
				return false;
			}
			
			return true;
		}

		public function getErrorMessages() {
			return $this->errorMessages;
		}
	}
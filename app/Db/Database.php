<?php
	
	namespace App\Db;

	use \PDO;
	use \PDOException;

	class Database{

		private static $host;
		private static $name;
		private static $user;
		private static $pass;
		private static $port;
		private $table;
		private $connection;
		private $finaly;
		private $whereClouse;

		public static function config($host, $name, $user, $pass, $port = 3306){
			self::$host = $host;
			self::$name = $name;
			self::$user = $user;
			self::$pass = $pass;
			self::$port = $port;
		}
		/**
		 * METODO RESPONSAVEL POR OBTER O NOME DA TABELA NA BASE DE DADOS.
		 * ESSE METODO OBTEM O NOME DA TABELA NA BASE DE DADOS CORECTAMENTE QUANDO A TABELA TEM 
		 * O MESMO NOME COM O MODELO, COM A ADICAO DO 'S' NO FINAL DO NOME.
		 * EXEMPLO: NOME DO MODELO( Model ), NOME DA TABELA NA BASE DE DADOS ( models ) 
		 * @return string
		 */
		public function getTableName() {
			$table = get_class($this);
			$statement = '\,-';
			$exTable = explode(explode(',', $statement)[0], $table);
			$table = $exTable[count($exTable) - 1];
			$table = strtolower($table).'s';
			return $table;
		}

		/**
		 * CONSTRUTOR DA CLASSE
		 * @param string $table
		 */
		public function __construct($table = null){
			$this->table = $table ?? $this->getTableName();
			$this->setConnection();  
		} 

		private function setConnection(){
			
			try {
				$this->connection = new PDO('mysql:host='.self::$host.';dbname='.self::$name.';port='.self::$port,self::$user,self::$pass);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
			} catch (PDOException $e) {
				//self::log()->emergency($e->getMessage()." DATA: ".date('Y/m/d H:i:s'));

				die('ERROR: '.$e->getMessage());
			}
		}
		public function execute($query, $params = []){
			

			try {
				$statement = $this->connection->prepare($query);
				$statement->execute($params);
				return $statement;
			} catch (PDOException $e) {
				//self::log()->alert($e->getMessage()."/// DATA: ".date('Y/m/d H:i:s').", URI: ".$_SERVER['REQUEST_URI'].", HOST: ".$_SERVER['HTTP_HOST'].", METHOD: ".$_SERVER['REQUEST_METHOD'].", USER_AGENT: ".$_SERVER['HTTP_USER_AGENT'], ["logger" => true]);

				die('ERROR: '.$e->getMessage());
			}
		}
		public function insert($values){
			$fields = array_keys($values);
			$binds = array_pad([], count($fields), '?');

			//MONTA A QUERY
			$query = 'INSERT INTO '.$this->table.' ('.implode(',', $fields).') VALUES ('.implode(',',$binds).')';

			$this->execute($query, array_values($values));

			return $this->connection->lastInsertId();
		}
		public function select($where = "",$order = "", $limit = "", $fields = '*', $inner = ""){
			$where = strlen($where) ? 'WHERE '.$where : '';
			$order = strlen($order) ? 'ORDER BY '.$order : '';
			$limit = strlen($limit) ? 'LIMIT '.$limit : '';


			//MONTA A QUERY
            if(strlen($inner)) {
                $query = 'SELECT * FROM'.$this->table.' '.$inner.' '.$order.' '.$limit;
            } else {
                $query = 'SELECT '.$fields.' FROM '.$this->table.' '.$where.' '.$order.' '.$limit;
            }


			return $this->execute($query);
		}

		/**
		 * METODO RESPONSAVEL POR BUSCAR DADOS NA DATABASE
		 * @param string $fields
		 * @return $this
		 */
		public function read( $fields = '*' ) {
			if (!is_array($fields)) $fields = $fields;
			else $fields = implode(', ', $fields);

			$query = 'SELECT '.$fields.' FROM '.$this->table;
			$this->finaly = $query;
			return $this;
		}
		/**
		 * METODO RESPONSAVEL POR SELECIONAR PARTE DOS DADOS DE SELECAO
		 * @param string ...$parmas
		 * @return $this
		 */
		public function where( string ...$params ) {
			$field    = $params[0];
			$operator = (count($params) > 2) ? $params[1] : '=';
			$value    = isset($params[2]) ? $params[2] : $params[1];
			$value    = is_numeric($value) ? $value : "'".$value."'";
			$where    = preg_match('/WHERE/', $this->finaly ?? '') ? 'OR' : 'WHERE';
			$this->whereClouse = $where;
			// BUILD QUERY
			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
			$query = $query.' '.$where.' '.$field.' '.$operator.' '.$value;
			$this->whereClouse = $where.' '.$field.' '.$operator.' '.$value;

			$this->finaly = $query;
			
			return $this;
		}

		public function whereNotIn( string $colum, string $table, string $colum2 ) {
			// SELECT * FROM dados WHERE msisdn NOT IN (SELECT msisdn FROM allwinners)
			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
			$query = $query.' WHERE '.$colum.' NOT IN (SELECT '.$colum2.' FROM '.$table.')';
			
			$this->finaly = $query;
			
			return $this;
		}

		/**
		 * METODO RESPONSAVEL POR EXECUTAR INNER JOIN NA TABELA
		 * @param string $table
		 * @param string ...$params
		 * @return $this
		 */
		public function join(string $table, ...$params ) {

			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
		
			if (isset($params[1])) {
				$field1    = $params[0];
				$operator = (count($params) > 2) ? $params[1] : '=';
				$field2    = isset($params[2]) ? $params[2] : $params[1];

				// BUILD QUERY
				$query = $query.' INNER JOIN '.$table.' ON '.$field1.' '.$operator.' '.$field2;
				
			} else if(isset($params[0]) && is_array($params[0])) {
				$parseQuery = '';
				foreach ($params[0] as $value) {
					$operator = isset($value[2]) ? $value[1] : '=';
					$parseQuery = !empty($parseQuery) ? $parseQuery.' OR ' : '';
					$last = $value[2] ?? $value[1];
					$parseQuery = $parseQuery.' '.$value[0].' '.$operator.' '.$last;
				}
				$query = $query.' INNER JOIN '.$table.' ON '.$parseQuery;
			}
			
			$this->finaly = $query;

			return $this;
		}

		/**
		 * METODO RESPONSAVEL POR EXECUTAR INNER JOIN NA TABELA
		 * @param string $table
		 * @param string ...$params
		 * @return $this
		 */
		public function leftjoin( $table, ...$params) {

			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
		
			if (isset($params[1])) {
				$field1    = $params[0];
				$operator = (count($params) > 2) ? $params[1] : '=';
				$field2    = isset($params[2]) ? $params[2] : $params[1];

				// BUILD QUERY
				$query = $query.' LEFT JOIN '.$table.' ON '.$field1.' '.$operator.' '.$field2;
				
			} else if(isset($params[0]) && is_array($params[0])) {
				$parseQuery = '';
				foreach ($params[0] as $value) {
					$operator = isset($value[2]) ? $value[1] : '=';
					$parseQuery = !empty($parseQuery) ? $parseQuery.' OR ' : '';
					$last = $value[2] ?? $value[1];
					$parseQuery = $parseQuery.' '.$value[0].' '.$operator.' '.$last;
				}
				$query = $query.' LEFT JOIN '.$table.' ON '.$parseQuery;
			}

			$this->finaly = $query;

			return $this;
		}

		/**
		 * METODO RESPONSAVEL POR EXECUTAR INNER JOIN NA TABELA
		 * @param string $table
		 * @param string ...$params
		 * @return $this
		 */
		public function rightjoin( $table, ...$params) {

			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
		
			if (isset($params[1])) {
				$field1    = $params[0];
				$operator = (count($params) > 2) ? $params[1] : '=';
				$field2    = isset($params[2]) ? $params[2] : $params[1];

				// BUILD QUERY
				$query = $query.' RIGHT JOIN '.$table.' ON '.$field1.' '.$operator.' '.$field2;
				
			} else if(isset($params[0]) && is_array($params[0])) {
				$parseQuery = '';
				foreach ($params[0] as $value) {
					$operator = isset($value[2]) ? $value[1] : '=';
					$parseQuery = !empty($parseQuery) ? $parseQuery.' OR ' : '';
					$last = $value[2] ?? $value[1];
					$parseQuery = $parseQuery.' '.$value[0].' '.$operator.' '.$last;
				}
				$query = $query.' RIGHT JOIN '.$table.' ON '.$parseQuery;
			}

			$this->finaly = $query;

			return $this;
		}

		/**
		 * METODO RESPONSAVEL POR DEFINIR A ORDEM DE RETORNO DO DADOS DA BASE DE DADOS
		 * @param string $field
		 * @return $this
		 */
		public function orderByDesc( string $field ) {
			// BUILD QUERY
			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
			$query = $query.' ORDER BY '.$field.' DESC';

			// EXECUTA A QUERY
			$this->finaly = $query;

			return $this;
		}

		/**
		 * METODO RESPONSAVEL POR DEFINIR UM LIMITE DE RETORNO DO DADOS DA BASE DE DADOS
		 * @param int $number
		 * @return $this
		 */
		public function limit( int $number ) {
			// BUILD QUERY
			$query = $this->finaly ?? 'SELECT * FROM '.$this->table;
			$query = $query.' LIMIT '.$number;

			// EXECUTA A QUERY
			$this->finaly = $query;

			return $this;
		}
		/**
		 * METODO RESPONSAVEL POR RETORNAR PARA A CLASSE A LISTAEM DOS DADOS
		 * @return Object $queue
		 */
		public function get() {
			$database_result = $this->execute($this->finaly);
 			$queue = [];
			while($data = $database_result->fetchObject(self::class)) $queue[] = $data;
			return $queue;
		}

		/**
		 * METODO RESPONSAVEL POR RETORNAR O PRIMEIRO RESULTADO DA CONSULTA
		 */
		public function first() {
            $database_result = $this->execute($this->finaly);
			return $database_result->fetchObject(self::class) ?? null;
		}

		/**
		 * METODO RESPONSAVEL POR ENCONTRAR ATRAVEZ DO ID UM DADO NO BANCO DE DADOS
		 * @param int $id
		 * @param string $colum
		 * @return 
		 */
		// public function find( int $id, string $colum = 'id' ) {
		// 	self::$class = get_class($this);
		// 	// BUILD QUERY 
		// 	$query = 'SELECT * FROM '.$this->table.' WHERE '.$colum.' = '.$id;

		// 	return $this->execute($query)->fetchObject(self::class) ?? '';
		// }

		// public function save() {
		// 	$data = get_class_vars(self::$class);
		// 	print_r($data); exit;
		// }
		
		public function update($where,$values){
			//DADOS DA QUERY
			$fields = array_keys($values);
 
			//MONTA A QUERY
			$query = 'UPDATE '.$this->table.' SET '.implode('=?,',$fields).'=? WHERE '.$where;
		
			//EXECUTAR A QUERY
			$this->execute($query,array_values($values));
		
			//RETORNA SUCESSO
			return true;
		}
		public function delete($where = null) {
			$where = $this->whereClouse ?? $where;
			//MONTA A QUERY
			$query = 'DELETE FROM '.$this->table.' '.$where;
	
			//EXECUTA A QUERY 
			$this->execute($query);

			return true;
		}

		public function setTableName( string $table ) {
			$this->table = $table;
		}

	}
<?php
	require __DIR__.'/../vendor/autoload.php';
	
	use \App\Utils\View;
	use \App\DotEnv\Environment;
	use \App\Db\Database;
	use \App\Http\Middleware\Queue as MiddlewareQueue;

	//CARREGA AS VARIAVEIS DO AMBIENTE
	Environment::load(__DIR__.'/../');

	//DEFINE AS CONFIGURACOES DO BANCO
	Database::config(
		getenv('DB_HOST'),
		getenv('DB_NAME'),
		getenv('DB_USER'),
		getenv('DB_PASS'),
		getenv('DB_PORT')
	);

	define('URL',getenv('URL'));
	
	function session($param) {
		if (!defined($param))
			define($param, \App\Helper\ErrorMessage::get($param));
		
		return constant($param);
	}

	function user() {
		
		$obj = new \App\Helper\Auth;
		return $obj->user();
	}

	define('error', \App\Helper\ErrorMessage::get());
	View::init([
		'URL'   => URL,
	]);

	//DEFINE O MAPEAMENTO DE MIDDLEWARE
	MiddlewareQueue::setMap([
		'maintenance'         => \App\Http\Middleware\Maintenance::class,
		'require-admin-login' => \App\Http\Middleware\RequireAdminLogin::class,
		'require-user-login'  => \App\Http\Middleware\RequireUserLogin::class,
		'require-user-logout' => \App\Http\Middleware\RequireUserLogout::class,
		'api'                 => \App\Http\Middleware\Api::class,
		'user-basic-auth'     => \App\Http\Middleware\UserBasicAuth::class,
		'cache'               => \App\Http\Middleware\Cache::class,
		'weekend-report'      => \App\Http\Middleware\WeekendReport::class
	]);
	//DEFINE O MAPEAMENTO DE MIDDLEWARE PADRAO (EXECUTADO EM TODO PROJECTO)
	MiddlewareQueue::setDefault([
		'maintenance',
		//'weekend-report'
	]);
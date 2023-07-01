<?php
	require __DIR__.'/includes/app.php';
	include __DIR__.'/vendor/autoload.php';
	
	use \App\Utils\View;
	use \App\Http\Router;
	
	$obRouter = new Router(URL);
	
	include __DIR__.'/routes/pages.php';
	include __DIR__.'/routes/api.php';

	$obRouter->run()->sendResponse();
exit;
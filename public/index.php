<?php

namespace Swiftlet;

try {
	chdir(dirname(__FILE__) . '/..');

	// Bootstrap the application
	require 'Swiftlet/Interfaces/App.php';
	require 'Swiftlet/App.php';

	$app = new App;

	set_error_handler(array($app, 'error'), E_ALL | E_STRICT);

	spl_autoload_register(array($app, 'autoload'));

	require 'config/main.php';

	date_default_timezone_set('UTC');

	$app->run();
	$app->serve();
} catch ( Exception $e ) {
	if ( !headers_sent() ) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
	}

	exit('Swiftlet Exception: ' . $e->getMessage());
}

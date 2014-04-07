<?php

namespace Swiftlet;

try {
	chdir(dirname(__FILE__) . '/..');

	// Bootstrap the application
	require 'vendor/Swiftlet/App.php';

	$app = new App;

	// Convert errors to ErrorException instances
	set_error_handler(array($app, 'error'), E_ALL | E_STRICT);

	spl_autoload_register(array($app, 'autoload'));

	require 'config/main.php';

	date_default_timezone_set('UTC');

	$view = new View;

	$app->loadPlugins('\HelloWorld\Plugins'); // You may comment this out if you're not using plugins

	$app->dispatchController('\HelloWorld\Controllers', $view);

	ob_start();

	$view->render('HelloWorld');

	ob_end_flush();
} catch ( \Exception $e ) {
	if ( !headers_sent() ) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
	}

	$errorCode = substr(sha1(uniqid(mt_rand(), true)), 0, 5);

	file_put_contents('log/exceptions.log', $errorCode . date(' r ') . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n", FILE_APPEND);

	exit('Exception: ' . $errorCode . '<br><br><small>The issue has been logged. Please contact the website administrator.</small>');
}

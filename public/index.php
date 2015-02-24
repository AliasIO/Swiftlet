<?php

namespace Swiftlet;

try {
	chdir(dirname(__FILE__) . '/..');

	// Bootstrap the application
	require 'vendor/Swiftlet/App.php';
	require 'vendor/Swiftlet/View.php';

	$app = new App(new View, 'HelloWorld');

	// Convert errors to ErrorException instances
	set_error_handler(array($app, 'error'), E_ALL | E_STRICT);

	spl_autoload_register(array($app, 'autoload'));

	require 'config/main.php';

	date_default_timezone_set('UTC');

	$app->loadPlugins(); // You may comment this out if you're not using plugins

	$app->dispatchController();

	ob_start();

	// To specify a directory, add it as an argument. For a demo, enter 'demo'.
	$app->serve();

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

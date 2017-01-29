<?php

declare(strict_types=1);

namespace Swiftlet;

chdir(dirname(__FILE__) . '/..');

require 'vendor/autoload.php';

use \Swiftlet\Factories\App as AppFactory;
use \Swiftlet\Factories\View as ViewFactory;

try {
	$view = ViewFactory::build();

	$app = AppFactory::build($view, 'HelloWorld');

	// Convert errors to ErrorException instances
	set_error_handler([ $app, 'error' ], E_ALL | E_STRICT);

	require 'config/main.php';

	date_default_timezone_set('UTC');

	$app->loadListeners(); // You may comment this out if you're not using listeners

	$app->dispatchController();

	ob_start();

	$view->render();

	ob_end_flush();
} catch ( \Exception $e ) {
	if ( !headers_sent() ) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
	}

	$errorCode = bin2hex(random_bytes(3));

	$errorMessage = $errorCode . date(' r ') . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

	file_put_contents('log/exceptions.log', "\n" . $errorMessage . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

	exit('Exception: ' . $errorCode . '<br><br><small>The issue has been logged. Please contact the website administrator.</small>');
}

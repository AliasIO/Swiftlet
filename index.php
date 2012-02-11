<?php

namespace Swiftlet;

try {
	// Bootstrap the application
	require 'Swiftlet/Interfaces/App.php';
	require 'Swiftlet/App.php';

	$app = new App;

	require 'config.php';

	$app->serve();
} catch ( \Exception $e ) {
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');

	exit('Swiftlet Exception: ' . $e->getMessage());
}

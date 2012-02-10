<?php

try {
	require 'Swiftlet/Interfaces/App.php';
	require 'Swiftlet/App.php';

	spl_autoload_register('Swiftlet\App::autoload');

	require 'config.php';

	Swiftlet\App::run();
	Swiftlet\App::serve();
} catch ( \Exception $e ) {
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');

	exit('Swiftlet Exception: ' . $e->getMessage());
}

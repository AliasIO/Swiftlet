<?php

try {
	require('app/App.php');
	require('app/Model.php');
	require('app/View.php');
	require('app/Controller.php');
	require('app/Plugin.php');

	Swiftlet\App::run();
} catch ( Exception $e ) {
	header('HTTP/1.1 503 Service Temporarily Unavailable');

	exit('Swiftlet Exception: ' . $e->getMessage());
}

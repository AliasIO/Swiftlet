<?php

try {
	require('app/Swiftlet.php');
	require('app/SwiftletModel.php');
	require('app/SwiftletController.php');
	require('app/SwiftletView.php');
	require('app/SwiftletPlugin.php');

	new Swiftlet;
} catch ( Exception $e ) {
	header('HTTP/1.1 503 Service Temporarily Unavailable');

	exit('Swiftlet Exception: ' . $e->getMessage());
}

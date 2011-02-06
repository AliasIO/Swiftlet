<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

/**
 * Check PHP version
 */
if ( version_compare(PHP_VERSION, '5.3', '<') )
{
	header('HTTP/1.1 503 Service Temporarily Unavailable');

	die('
		<p style="
			background: #FFC;
			border: 1px solid #DD7;
			border-radius: 3px;
			-moz-border-radius: 3px;
			-webkit-border-radius: 3px;
			color: #300;
			font-family: monospace;
			padding: 1em;
			">
			Sorry, PHP 5.3 or higher is required to run Swiftlet. The server is running version ' . PHP_VERSION . '.
		</p>
		');
}

/*
 * Bootstrapping
 */
$swiftlet = TRUE;

require('_app/Application.php');
require('_app/Plugin.php');
require('_app/Controller.php');
require('_app/View.php');

new Application;

exit;

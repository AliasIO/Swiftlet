<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

$config = array(
	/**
	 * Website settings
	 */
	'siteName'        => 'Swiftlet',
	'siteCopyright'   => '',
	'siteDesigner'    => '',
	'siteDescription' => '',
	'siteKeywords'    => '',

	/**
	 * Administrative e-mail address
	 */
	'adminEmail' => '',

	/**
	 * System password. Required for operations like installing plugins
	 */
	'sysPassword' => '',

	/**
	 * Cache dynamic pages to improve load times
	 */
	'caching' => FALSE, // TRUE | FALSE
	
	/**
	 * URL rewrites, working .htaccess file required
	 */
	'urlRewrite' => TRUE // TRUE | FALSE
	);

/*
 * testing should be set to FALSE when running in a production environment
 */
switch ( $app->userIp )
{
	case '127.0.0.1':
	case '0.0.0.0':
	case '::1':
		$config['testing'] = TRUE;
		
		break;
	default:
		$config['testing'] = FALSE;
}

/**
 * MySQL Database settings
 * Leave dbName empty if no database is used
 */
if ( $config['testing'] )
{
	/**
	 * Settings for local development and testing environment
	 */
	$config += array(
		'dbHost'    => 'localhost',
		'dbUser'    => '',
		'dbPass'    => '',
		'dbName'    => '',
		'dbPrefix'  => 'sw_'
		);
}
else
{
	/**
	 * Settings for production environment
	 */
	$config += array(
		'dbHost'    => 'localhost',
		'dbUser'    => '',
		'dbPass'    => '',
		'dbName'    => '',
		'dbPrefix'  => 'sw_'
		);
}

/*
 * debugMode should be set to FALSE when running in a production environment
 */
$config['debugMode'] = $config['testing']; // TRUE | FALSE

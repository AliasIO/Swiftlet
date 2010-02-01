<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

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
	 * System password. Required for some operations like installing plug-ins.
	 */
	'sysPassword' => 'foo',

	/**
	 * debugMode should be set to FALSE when running in a production environment.
	 */
	'debugMode' => TRUE, // TRUE | FALSE

	/**
	 * URL rewrites.
	 */
	'urlRewrite' => TRUE // TRUE | FALSE
	);

/**
 * MySQL Database settings
 * Leave dbName empty if no database is used
 */
switch ( $model->userIp )
{
	/**
	 * Settings for local development and testing environment
	 */
	case '127.0.0.1':
	case '0.0.0.0':
	case '::1':
		$config += array(
			'dbHost'    => 'localhost',
			'dbUser'    => 'root',
			'dbPass'    => '',
			'dbName'    => 'swiftlet',
			'dbPrefix'  => 'sw_',
			'dbCaching' => TRUE
			);

		break;
	/**
	 * Settings for production environment
	 */
	default:
		$config += array(
			'dbHost'    => 'localhost',
			'dbUser'    => 'root',
			'dbPass'    => '',
			'dbName'    => '',
			'dbPrefix'  => 'sw_',
			'dbCaching' => TRUE
			);
}

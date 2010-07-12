<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'info':
		$info = array(
			'name'       => 'log',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('init' => 1, 'end' => 1)
			);

		break;
	case 'init':
		require($contr->classPath . 'log.php');

		$app->log = new log($app);

		break;
	case 'unit_tests':
		$app->log->write('unit_test', 'Test');

		$params[] = array(
			'test' => 'Writing a log file to <code>/log/</code>.',
			'pass' => is_file($contr->rootPath . 'log/unit_test')
			);

		if ( is_file($contr->rootPath . 'log/unit_test') )
		{
			unlink($contr->rootPath . 'log/unit_test');
		}

		break;
}

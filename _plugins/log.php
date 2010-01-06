<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'load':
		$pluginVersion = '1.0.0';

		$compatible = array('from' => '1.2.0', 'to' => '1.2.*');

		$model->hook_register($plugin, array('init' => 1, 'unit_tests' => 1));

		break;	
	case 'init':
		if ( !is_dir($contr->rootPath . 'log') )
		{
			$this->model->error(FALSE, 'Directory "/log" does not exist.', __FILE__, __LINE__);
		}

		if ( !is_writable($contr->rootPath . 'log') )
		{
			$this->model->error(FALSE, 'Directory "/log" is not writable.', __FILE__, __LINE__);
		}

		require($contr->classPath . 'log.php');

		$model->log = new log($model);

		break;
	case 'unit_tests':
		$model->log->write('unit_test', 'Test');

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
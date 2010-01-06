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

		$dependencies = array('db', 'session');

		$model->hook_register($plugin, array('init' => 3, 'unit_tests' => 1));

		break;
	case 'init':
		if ( !empty($model->session->ready) )
		{
			require($contr->classPath . 'admin.php');

			$model->admin = new admin($model);
		}

		break;
	case 'unit_tests':
		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'admin/index.php', array(), TRUE);

		$params[] = array(
			'test' => '<code>/admin/</code> should be inaccessible for guests.',
			'pass' => $r['info']['http_code'] == '302'
			);

		break;
}
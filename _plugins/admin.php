<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'info':
		$info = array(
			'name'         => 'admin',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'perm', 'session'),
			'hooks'        => array('init' => 5, 'install' => 1, 'remove' => 1, 'unit_tests' => 1)
			);

		break;
	case 'install':
		if ( !empty($model->perm->ready) )
		{
			$model->perm->create('Administration', 'admin access', 'Access to the administration overview');
		}

		break;
	case 'remove':
		if ( !empty($model->perm->ready) )
		{
			$model->perm->delete('admin access');
		}

		break;
	case 'init':
		if ( !empty($model->perm->ready) )
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
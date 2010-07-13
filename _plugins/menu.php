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
			'name'         => 'menu',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('node'),			
			'hooks'        => array('dashboard' => 3, 'init' => 5, 'install' => 1, 'menu' => 1, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($app->db->prefix . 'menu', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'menu` (
					`items` TEXT NULL
					) TYPE = INNODB
				;');
			
			$app->db->sql('
				INSERT INTO `' . $app->db->prefix . 'menu` (
					`items`
					)
				VALUES (
					""
					)
				;');
		}

		if ( !empty($app->permission->ready) )
		{
			$app->permission->create('Menu', 'admin menu access', 'Manage menu items');
		}

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'menu', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'menu`;');
		}

		if ( !empty($app->permission->ready) )
		{
			$app->permission->delete('admin menu access');
		}

		break;
	case 'init':
		require($controller->classPath . 'menu.php');

		$app->menu = new menu($app);

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Menu',
			'description' => 'Add and remove menu items',
			'group'       => 'Content',
			'path'        => 'admin/menu/',
			'permission'        => 'admin menu access'
			);

		break;
	case 'menu':
		if ( !empty($app->menu->ready) )
		{
			$app->menu->get_items($params);
		}

		break;
}

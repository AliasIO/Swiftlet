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
			'name'         => 'menu',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('node'),			
			'hooks'        => array('dashboard' => 3, 'init' => 5, 'install' => 1, 'menu' => 1, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'menu', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'menu` (
					`items` TEXT NULL
					)
				;');
			
			$model->db->sql('
				INSERT INTO `' . $model->db->prefix . 'menu` (
					`items`
					)
				VALUES (
					""
					)
				;');
		}

		if ( !empty($model->perm->ready) )
		{
			$model->perm->create('Menu', 'admin menu access', 'Manage menu items');
		}

		break;
	case 'remove':
		if ( in_array($model->db->prefix . 'menu', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'menu`;');
		}

		if ( !empty($model->perm->ready) )
		{
			$model->perm->delete('admin menu access');
		}

		break;
	case 'init':
		require($contr->classPath . 'menu.php');

		$model->menu = new menu($model);

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Menu',
			'description' => 'Add and remove menu items',
			'group'       => 'Content',
			'path'        => 'admin/menu/',
			'perm'        => 'admin menu access'
			);

		break;
	case 'menu':
		if ( !empty($model->menu->ready) )
		{
			$model->menu->get_items($params);
		}

		break;
}

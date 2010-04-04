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
			'name'         => 'perm',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('session', 'user'),
			'hooks'        => array('dashboard' => 4, 'init' => 4, 'install' => 1, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'perms', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'perms` (
					`id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`name`  VARCHAR(255)     NOT NULL,
					`desc`  VARCHAR(255)     NOT NULL,
					`group` VARCHAR(255)     NOT NULL,
					UNIQUE `name` (`name`),
					PRIMARY KEY (`id`)
					)
				;');

			$model->db->sql('
				INSERT INTO `' . $model->db->prefix . 'perms` (
					`name`,
					`desc`,
					`group`
					)
				VALUES (
					"admin perm access",
					"Manage roles",
					"Permissions"
				),
				(
					"admin perm create",
					"Create roles",
					"Permissions"
				),(
					"admin perm edit",
					"Edit roles",
					"Permissions"
				),(
					"admin perm delete",
					"Delete roles",
					"Permissions"
				)
				;');
		}

		if ( !in_array($model->db->prefix . 'perms_roles', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'perms_roles` (
					`id`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`name` VARCHAR(255)     NOT NULL,
					UNIQUE `name` (`name`),
					PRIMARY KEY (`id`)
					)
				;');

			$model->db->sql('
				INSERT INTO `' . $model->db->prefix . 'perms_roles` (
					`name`
					)
				VALUES (
					"Administrator"
					)
				;');
		}

		if ( !in_array($model->db->prefix . 'perms_roles_xref', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'perms_roles_xref` (
					`perm_id` INT(10) UNSIGNED NOT NULL,
					`role_id` INT(10) UNSIGNED NOT NULL,
					`value`   INT(1)               NULL,
					UNIQUE `perm_user` (`perm_id`, `role_id`)
					)
				;');
		}

		if ( !in_array($model->db->prefix . 'perms_roles_users_xref', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'perms_roles_users_xref` (
					`role_id` INT(10) UNSIGNED NOT NULL,
					`user_id` INT(10) UNSIGNED NOT NULL,
					UNIQUE `role_user` (`role_id`, `user_id`)
					)
				;');
		}

		break;
	case 'remove':
		if ( in_array($model->db->prefix . 'perms', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'perms`;');
		}

		if ( in_array($model->db->prefix . 'perms_roles', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'perms_roles`;');
		}

		if ( in_array($model->db->prefix . 'perms_roles_xref', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'perms_roles_xref`;');
		}

		if ( in_array($model->db->prefix . 'perms_roles_users_xref', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'perms_roles_users_xref`;');
		}

		break;
	case 'init':
		if ( !empty($model->session->ready) )
		{
			require($contr->classPath . 'permission.php');

			$model->perm = new perm($model);
		}

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Permsissions',
			'description' => 'Add and edit roles and permissions',
			'group'       => 'Users',
			'path'        => 'admin/perms/',
			'perm'        => 'admin perm access'
			);

		break;
}

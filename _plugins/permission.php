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
			'name'         => 'perm',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('session', 'user'),
			'hooks'        => array('dashboard' => 5, 'init' => 4, 'install' => 1, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($app->db->prefix . 'perms', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'perms` (
					`id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`name`  VARCHAR(255)     NOT NULL,
					`desc`  VARCHAR(255)     NOT NULL,
					`group` VARCHAR(255)     NOT NULL,
					UNIQUE `name` (`name`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');

			$app->db->sql('
				INSERT INTO `' . $app->db->prefix . 'perms` (
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

		if ( !in_array($app->db->prefix . 'perms_roles', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'perms_roles` (
					`id`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`name` VARCHAR(255)     NOT NULL,
					UNIQUE `name` (`name`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');

			$app->db->sql('
				INSERT INTO `' . $app->db->prefix . 'perms_roles` (
					`name`
					)
				VALUES (
					"Administrator"
					)
				;');
		}

		if ( !in_array($app->db->prefix . 'perms_roles_xref', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'perms_roles_xref` (
					`perm_id` INT(10) UNSIGNED NOT NULL,
					`role_id` INT(10) UNSIGNED NOT NULL,
					`value`   INT(1)               NULL,
					UNIQUE `perm_user` (`perm_id`, `role_id`)
					) TYPE = INNODB
				;');
		}

		if ( !in_array($app->db->prefix . 'perms_roles_users_xref', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'perms_roles_users_xref` (
					`role_id` INT(10) UNSIGNED NOT NULL,
					`user_id` INT(10) UNSIGNED NOT NULL,
					UNIQUE `role_user` (`role_id`, `user_id`)
					) TYPE = INNODB
				;');
		}

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'perms', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'perms`;');
		}

		if ( in_array($app->db->prefix . 'perms_roles', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'perms_roles`;');
		}

		if ( in_array($app->db->prefix . 'perms_roles_xref', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'perms_roles_xref`;');
		}

		if ( in_array($app->db->prefix . 'perms_roles_users_xref', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'perms_roles_users_xref`;');
		}

		break;
	case 'init':
		if ( !empty($app->session->ready) )
		{
			require($contr->classPath . 'permission.php');

			$app->perm = new perm($app);
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

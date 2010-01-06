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

		$model->hook_register($plugin, array('admin' => 1, 'init' => 3, 'unit_tests' => 1));

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'users', $model->db->tables) )
		{
			$description = 'A user will be created with username "Admin" and system password.';

			$sql = array('
				CREATE TABLE `' . $model->db->prefix . 'users` (
					`id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`username`  VARCHAR(255)     NOT NULL,
					`pass_hash` VARCHAR(40)      NOT NULL,
					`email`     VARCHAR(255)     NULL,
					`auth`      INT(1)           NOT NULL,
					`date`      DATETIME         NOT NULL,
					`date_edit` DATETIME         NOT NULL,
					INDEX `username` (`username`),
					PRIMARY KEY (`id`)
					);
				', '
				INSERT INTO `' . $model->db->prefix . 'users` (
					`username`,
					`pass_hash`,
					`auth`,
					`date`,
					`date_edit`
					)
				VALUES (
					"Admin",
					"' . sha1('swiftlet' . strtolower('Admin') . $model->sysPassword) . '",
					4,
					NOW(),
					NOW()
					);
				', '
				CREATE TABLE `' . $model->db->prefix . 'user_prefs` (
					`id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`pref`   VARCHAR(255)     NOT NULL,
					`type`   VARCHAR(255)     NOT NULL,
					`match`  VARCHAR(255)     NOT NULL,
					`values` VARCHAR(255)     NOT NULL,
					UNIQUE `pref` (`pref`),
					PRIMARY KEY (`id`)
					);
				', '
				CREATE TABLE `' . $model->db->prefix . 'user_prefs_xref` (
					`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`user_id` INT(10)          NOT NULL,
					`pref_id` INT(10)          NOT NULL,
					`value`   VARCHAR(255)     NOT NULL,
					UNIQUE `user_pref_id` (`user_id`, `pref_id`),
					PRIMARY KEY (`id`)
					);
				');
		}

		break;
	case 'init':
		if ( !empty($model->session->ready) )
		{
			require($contr->classPath . 'user.php');
			
			$model->user = new user($model);
		}

		break;
	case 'admin':
		$params[] = array(
			'name'        => 'Accounts',
			'description' => 'Add and edit accounts',
			'group'       => 'Users',
			'path'        => 'account/',
			'auth'        => 1,
			'order'       => 1
			);
		
		break;
	case 'unit_tests':
		/**
		 * Creating a user account
		 */
		$post = array(
			'username'         => 'Unit_Test',
			'password'         => '123',
			'password_confirm' => '123',
			'auth'             => '1',
			'form-submit'      => 'Submit',
			'auth_token'       => $model->authToken
			);

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'account/?action=create', $post);

		$model->db->sql('
			SELECT
				*
			FROM `' . $model->db->prefix . 'users`
			WHERE
				`username` = "Unit_Test"
			LIMIT 1
			;', FALSE);

		$user = isset($model->db->result[0]) ? $model->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Creating a user account in <code>/account/</code>.',
			'pass' => ( bool ) $user['id']
			);

		/**
		 * Editing a user account
		 */
		if ( $user['id'] )
		{
			$post = array(
				'username'    => $user['username'],
				'auth'        => $user['auth'],
				'email'       => 'unit@test.com',
				'form-submit' => 'Submit',
				'auth_token'  => $model->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'account/?id=' . ( int ) $user['id'], $post);
		}

		$model->db->sql('
			SELECT
				`email`
			FROM `' . $model->db->prefix . 'users`
			WHERE
				`id` = ' . ( int ) $user['id'] . '
			LIMIT 1
			;', FALSE);

		$email = isset($model->db->result[0]) ? $model->db->result[0]['email'] : FALSE;

		$params[] = array(
			'test' => 'Editing a user account in <code>/account/</code>.',
			'pass' => $email == 'unit@test.com'
			);

		/**
		 * Deleting a user account
		 */
		if ( $user['id'] )
		{
			$post = array(
				'get_data'   => serialize(array(
					'id'     => ( int ) $user['id'],
					'action' => 'delete'
					)),
				'confirm'    => '1',
				'auth_token' => $model->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'account/?id=' . ( int ) $user['id'] . '&action=delete', $post);
		}

		$model->db->sql('
			SELECT
				`id`
			FROM `' . $model->db->prefix . 'users`
			WHERE
				`id` = ' . ( int ) $user['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a user account <code>/account/</code>.',
			'pass' => !$model->db->result
			);
		
		break;
}
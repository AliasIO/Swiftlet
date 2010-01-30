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
			'name'         => 'user',
			'description'  => 'A user will be created with username "Admin" and system password.',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'session'),
			'hooks'        => array('admin' => 2, 'init' => 3, 'install' => 1, 'unit_tests' => 1, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'users', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'users` (
					`id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`username`  VARCHAR(255)     NOT NULL,
					`email`     VARCHAR(255)     NULL,
					`owner`     INT(1)           NOT NULL,
					`date`      DATETIME         NOT NULL,
					`date_edit` DATETIME         NOT NULL,
					`salt`      VARCHAR(64)      NOT NULL,
					`pass_hash` VARCHAR(64)      NOT NULL,
					UNIQUE `username` (`username`),
					PRIMARY KEY (`id`)
					)
				;');

			$salt     = hash('sha256', uniqid(mt_rand(), true));	
			$passHash = hash('sha256', 'swiftlet' . $salt . 'admin' . $model->sysPassword);

			$model->db->sql('
				INSERT INTO `' . $model->db->prefix . 'users` (
					`username`,
					`owner`,
					`date`,
					`date_edit`,
					`salt`,
					`pass_hash`
					)
				VALUES (
					"Admin",
					1,
					"' . gmdate('Y-m-d H:i:s') . '",
					"' . gmdate('Y-m-d H:i:s') . '",
					"' . $salt . '",
					"' . $passHash . '"
					)
				;');
		}

		if ( !in_array($model->db->prefix . 'user_prefs', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'user_prefs` (
					`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`pref`    VARCHAR(255)     NOT NULL,
					`type`    VARCHAR(255)     NOT NULL,
					`match`   VARCHAR(255)     NOT NULL,
					`options` TEXT                 NULL,
					UNIQUE `pref` (`pref`),
					PRIMARY KEY (`id`)
					)
				;');
		}

		if ( !in_array($model->db->prefix . 'user_prefs_xref', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'user_prefs_xref` (
					`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`user_id` INT(10)          NOT NULL,
					`pref_id` INT(10)          NOT NULL,
					`value`   VARCHAR(255)     NOT NULL,
					UNIQUE `user_pref_id` (`user_id`, `pref_id`),
					PRIMARY KEY (`id`)
					)
				;');
		}

		break;
	case 'remove':
		if ( in_array($model->db->prefix . 'users', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'users`;');
		}

		if ( in_array($model->db->prefix . 'user_prefs', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'user_prefs`;');
		}

		if ( in_array($model->db->prefix . 'user_prefs_xref', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'user_prefs_xref`;');
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
			'order'       => 3
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
			'owner'            => '0',
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
				'owner'       => $user['owner'],
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

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
			'name'         => 'user',
			'description'  => 'A user will be created with username "Admin" and system password.',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'session'),
			'hooks'        => array('dashboard' => 4, 'init' => 3, 'install' => 1, 'menu' => 999, 'unit_tests' => 1, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($app->db->prefix . 'users', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'users` (
					`id`                 INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
					`username`           VARCHAR(255)        NOT NULL,
					`email`              VARCHAR(255)            NULL,
					`owner`              TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
					`date`               DATETIME            NOT NULL,
					`date_edit`          DATETIME            NOT NULL,
					`date_login_attempt` DATETIME NOT            NULL,
					`pass_hash`          VARCHAR(128)        NOT NULL,
					UNIQUE `username` (`username`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');

			$salt     = hash('sha256', uniqid(mt_rand(), true) . 'swiftlet' . 'admin');
			$passHash = $salt . $app->sysPassword;

			for ( $i = 0; $i < 100000; $i ++ )
			{
				$passHash = hash('sha256', $passHash);
			}

			$passHash = $salt . $passHash;

			$app->db->sql('
				INSERT INTO `' . $app->db->prefix . 'users` (
					`username`,
					`owner`,
					`date`,
					`date_edit`,
					`pass_hash`
					)
				VALUES (
					"Admin",
					1,
					"' . gmdate('Y-m-d H:i:s') . '",
					"' . gmdate('Y-m-d H:i:s') . '",
					"' . $passHash . '"
					)
				;');
		}

		if ( !in_array($app->db->prefix . 'user_prefs', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'user_prefs` (
					`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`pref`    VARCHAR(255)     NOT NULL,
					`type`    VARCHAR(255)     NOT NULL,
					`match`   VARCHAR(255)     NOT NULL,
					`options` TEXT                 NULL,
					UNIQUE `pref` (`pref`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

		if ( !in_array($app->db->prefix . 'user_prefs_xref', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'user_prefs_xref` (
					`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`user_id` INT(10)          NOT NULL,
					`pref_id` INT(10)          NOT NULL,
					`value`   VARCHAR(255)     NOT NULL,
					UNIQUE `user_pref_id` (`user_id`, `pref_id`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'users', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'users`;');
		}

		if ( in_array($app->db->prefix . 'user_prefs', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'user_prefs`;');
		}

		if ( in_array($app->db->prefix . 'user_prefs_xref', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'user_prefs_xref`;');
		}

		break;
	case 'init':
		if ( !empty($app->session->ready) )
		{
			require($contr->classPath . 'user.php');

			$app->user = new user($app);
		}

		break;
	case 'menu':
		if ( !empty($app->session->ready) )
		{
			if ( $app->session->get('user id') == user::guestId )
			{
				$params['Login'] = $view->rootPath . 'login/';
			}
			else
			{
				$params['Account'] = $view->rootPath . 'account/';
				$params['Log out (' .  $app->session->get('user username') . ')']  = $view->rootPath . 'login/?logout';
			}
		}

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Accounts',
			'description' => 'Add and edit accounts',
			'group'       => 'Users',
			'path'        => 'account/'
			);
		
		break;
	case 'unit_tests':
		/**
		 * Creating a user account
		 */
		$post = array(
			'username'             => 'Unit_Test',
			'new_password'         => '123',
			'new_password_confirm' => '123',
			'owner'                => '0',
			'form-submit'          => 'Submit',
			'auth-token'           => $app->authToken
			);

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'account/?action=create', $post);

		$app->db->sql('
			SELECT
				*
			FROM `' . $app->db->prefix . 'users`
			WHERE
				`username` = "Unit_Test"
			LIMIT 1
			;', FALSE);

		$user = isset($app->db->result[0]) ? $app->db->result[0] : FALSE;

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
				'password'    => '123',
				'owner'       => $user['owner'],
				'email'       => 'unit@test.com',
				'form-submit' => 'Submit',
				'auth-token'  => $app->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'account/?id=' . ( int ) $user['id'], $post);
		}

		$app->db->sql('
			SELECT
				`email`
			FROM `' . $app->db->prefix . 'users`
			WHERE
				`id` = ' . ( int ) $user['id'] . '
			LIMIT 1
			;', FALSE);

		$email = isset($app->db->result[0]) ? $app->db->result[0]['email'] : FALSE;

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
				'auth-token' => $app->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'account/?id=' . ( int ) $user['id'] . '&action=delete', $post);
		}

		$app->db->sql('
			SELECT
				`id`
			FROM `' . $app->db->prefix . 'users`
			WHERE
				`id` = ' . ( int ) $user['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a user account <code>/account/</code>.',
			'pass' => !$app->db->result
			);

		/**
		 * Creating a user preference
		 */
		$app->user->save_pref(array(
			'pref'    => 'Unit Test',
			'type'    => 'text',
			'match'   => '/.*/'
			));

		$app->db->sql('
			SELECT
				`id`
			FROM `' . $app->db->prefix . 'user_prefs`
			WHERE
				`pref` = "Unit Test"
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Creating a user preference.',
			'pass' => $app->db->result
			);

		/**
		 * Deleting a user preference
		 */
		$app->user->delete_pref('Unit Test');
		
		$app->db->sql('
			SELECT
				`id`
			FROM `' . $app->db->prefix . 'user_prefs`
			WHERE
				`pref` = "Unit Test"
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a user preference.',
			'pass' => !$app->db->result
			);

		break;
}

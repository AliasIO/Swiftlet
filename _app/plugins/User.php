<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class User_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'session'),
		$hooks        = array('dashboard' => 4, 'init' => 3, 'install' => 1, 'menu' => 999, 'unit_tests' => 1, 'remove' => 1);

	public
		$prefs = array()
		;

	const
		GUEST_ID = 0
		;

	/*
	 * Implement install hook
	 */
	function install()
	{
		if ( !in_array($this->app->db->prefix . 'users', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'users` (
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
			$passHash = $salt . $this->app->config['sysPassword'];

			for ( $i = 0; $i < 100000; $i ++ )
			{
				$passHash = hash('sha256', $passHash);
			}

			$passHash = $salt . $passHash;

			$this->app->db->sql('
				INSERT INTO `' . $this->app->db->prefix . 'users` (
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

		if ( !in_array($this->app->db->prefix . 'user_prefs', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'user_prefs` (
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

		if ( !in_array($this->app->db->prefix . 'user_prefs_xref', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'user_prefs_xref` (
					`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`user_id` INT(10)          NOT NULL,
					`pref_id` INT(10)          NOT NULL,
					`value`   VARCHAR(255)     NOT NULL,
					UNIQUE `user_pref_id` (`user_id`, `pref_id`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

	}

	/*
	 * Implement remove hook
	 */
	function remove()
	{
		if ( in_array($this->app->db->prefix . 'users', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE `' . $this->app->db->prefix . 'users`;');
		}

		if ( in_array($this->app->db->prefix . 'user_prefs', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE `' . $this->app->db->prefix . 'user_prefs`;');
		}

		if ( in_array($this->app->db->prefix . 'user_prefs_xref', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE `' . $this->app->db->prefix . 'user_prefs_xref`;');
		}
	}

	/*
	 * Implement menu hook
	 * @params array $params
	 */
	function menu(&$params)
	{
		if ( $this->app->session->get('user id') == User_Plugin::GUEST_ID )
		{
			$params['Login'] = 'login';
		}
		else
		{
			$params['Account'] = 'account';
			$params['Log out (' .  $this->app->session->get('user username') . ')']  = 'login/logout';
		}
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		/**
		 * Check if the users table exists
		 */
		if ( in_array($this->app->db->prefix . 'users', $this->app->db->tables) )
		{
			$this->ready = TRUE;

			if ( in_array($this->app->db->prefix . 'user_prefs', $this->app->db->tables) )
			{
				$this->app->db->sql('
					SELECT
						*
					FROM `' . $this->app->db->prefix . 'user_prefs' . '`
					;');

				if ( $r = $this->app->db->result )
				{
					foreach ( $r as $d )
					{
						$this->prefs[$d['pref']] = $d;

						$this->prefs[$d['pref']]['options'] = unserialize($d['options']);
					}
				}
			}

			$this->app->session->put('pref_values', $this->get_pref_values($this->app->session->get('user id')));

			/**
			 * Guest user
			 */
			if ( $this->app->session->get('user id') === FALSE )
			{
				$this->app->session->put(array(
					'user id'       => User_Plugin::GUEST_ID,
					'user username' => User_Plugin::GUEST_ID
					));
			}
		}
	}

	/*
	 * Implement dashboard hook
	 * @param array $params
	 */
	function dashboard(&$params)
	{
		$params[] = array(
			'name'        => 'Accounts',
			'description' => 'Add and edit accounts',
			'group'       => 'Users',
			'path'        => 'account'
			);
	}

	/**
	 * Login
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	function login($username, $password, $remember)
	{
		if ( $this->app->session->get('user id') !== FALSE )
		{
			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'users` SET
					`date_login_attempt` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`username` = "' . $this->app->db->escape($username) . '"
				LIMIT 1
				;');

			if ( $this->validate_password($username, $password) )
			{
				$this->app->db->sql('
					SELECT
						*
					FROM `' . $this->app->db->prefix . 'users`
					WHERE
						`username` = "' . $this->app->db->escape($username) . '"
					LIMIT 1
					;', FALSE);

				if ( !empty($this->app->db->result[0]) && $r = $this->app->db->result[0] )
				{
					$lifeTime = $remember ? 60 * 60 * 24 * 14 : $this->app->session->sessionLifeTime;

					$this->app->session->put(array(
						'user id'       => $r['id'],
						'user username' => $r['username'],
						'user email'    => $r['email'],
						'user is owner' => $r['owner'],
						'lifetime'      => $lifeTime
						));

					return TRUE;
				}
			}
		}
	}

	/**
	 * Logout
	 * @return bool
	 */
	function logout()
	{
		$this->app->session->reset();
		$this->app->session->end();
	}

	/**
 	 * Validate password
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	function validate_password($username, $password)
	{
		$this->app->db->sql('
			SELECT
				`pass_hash`
			FROM `' . $this->app->db->prefix . 'users`
			WHERE
				`username` = "' . $this->app->db->escape($username) . '"
			LIMIT 1
			;', FALSE);

		if ( !empty($this->app->db->result[0]) && $r = $this->app->db->result[0] )
		{
			$salt     = substr($r['pass_hash'], 0, 64);
			$passHash = $salt . $password;

			for ( $i = 0; $i < 100000; $i ++ )
			{
				$passHash = hash('sha256', $passHash);
			}

			$passHash = $salt . $passHash;

			if ( $passHash == $r['pass_hash'] )
			{
				$passHash = $this->make_pass_hash($username, $password);

				$this->app->db->sql('
					UPDATE `' . $this->app->db->prefix . 'users` SET
						`pass_hash` = "' . $passHash . '"
					WHERE
						`username` = "' . $this->app->db->escape($username) . '"
					LIMIT 1
					;');

				return true;
			}
		}
	}

	/**
 	 * Create a password hash
	 * @param string $username
	 * @param string $password
	 * @return string
	 */
	function make_pass_hash($username, $password)
	{
		$salt     = hash('sha256', uniqid(mt_rand(), true) . 'swiftlet' . strtolower($username));
		$passHash = $salt . $password;

		// Delay encryption by hashing many times, makes brute forcing more difficult
		for ( $i = 0; $i < 100000; $i ++ )
		{
			$passHash = hash('sha256', $passHash);
		}

		$passHash = $salt . $passHash;

		return $passHash;
	}

	/**
	 * Save a preference
	 * @param array $params
	 */
	function save_pref($params)
	{
		$params = array_merge(array(
			'pref'    => '',
			'type'    => 'text',
			'match'   => '/.*/',
			'options' => array()
			), $params);

		$this->app->db->sql('
			INSERT INTO `' . $this->app->db->prefix . 'user_prefs` (
				`pref`,
				`type`,
				`match`,
				`options`
				)
			VALUES (
				"' . $this->app->db->escape($params['pref'])               . '",
				"' . $this->app->db->escape($params['type'])               . '",
				"' . $this->app->db->escape($params['match'])              . '",
				"' . $this->app->db->escape(serialize($params['options'])) . '"
				)
			ON DUPLICATE KEY UPDATE
				`options` = "' . $this->app->db->escape(serialize($params['options'])) . '"
			;');
	}

	/**
	 * Delete a preference
	 * @param string $pref
	 */
	function delete_pref($pref)
	{
		$this->app->db->sql('
			DELETE
				up, upx
			FROM      `' . $this->app->db->prefix . 'user_prefs`      AS up
			LEFT JOIN `' . $this->app->db->prefix . 'user_prefs_xref` AS upx ON up.`id` = upx.`pref_id`
			WHERE
				up.`pref` = "' . $this->app->db->escape($pref) . '"
			;');
	}

	/**
	 * Save a preference value
	 * @param array $params
	 */
	function save_pref_value($params)
	{
		$this->app->db->sql('
			INSERT INTO `' . $this->app->db->prefix . 'user_prefs_xref` (
				`user_id`,
				`pref_id`,
				`value`
				)
			VALUES (
				 ' . ( int ) $params['user_id']                  . ',
				 ' . ( int ) $this->prefs[$params['pref']]['id'] . ',
				"' . $this->app->db->escape($params['value'])    . '"
				)
			ON DUPLICATE KEY UPDATE
				`value` = "' . $this->app->db->escape($params['value']) . '"
			;');

		if ( $this->app->db->result )
		{
			$params = array(
				'pref'  => $params['pref'],
				'value' => $params['value'],
				);
		}
	}

	/**
	 * Get a user's preferences
	 * @param int $id
	 */
	function get_pref_values($userId)
	{
		$prefs = array();

		if ( ( int ) $userId )
		{
			$this->app->db->sql('
				SELECT
					uo.`pref`,
					uox.`value`
				FROM      `' . $this->app->db->prefix . 'user_prefs`      AS uo
				LEFT JOIN `' . $this->app->db->prefix . 'user_prefs_xref` AS uox ON uo.`id` = uox.`pref_id`
				WHERE
					uox.`user_id` = ' . ( int ) $userId . '
				;');

			if ( $r = $this->app->db->result )
			{
				foreach ( $r as $d )
				{
					$prefs[$d['pref']] = $d['value'];
				}
			}
		}

		return $prefs;
	}

	/*
	 * Implement unit_tests hook
	 * @params array $params
	 */
	function unit_tests(&$params)
	{
		/**
		 * Creating a user account
		 */
		$post = array(
			'username'             => 'Unit_Test',
			'new_password'         => '123',
			'new_password_confirm' => '123',
			'owner'                => '0',
			'form-submit'          => 'Submit',
			'auth-token'           => $this->app->input->authToken
			);

		$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->absPath . 'account/create', $post);

		$this->app->db->sql('
			SELECT
				*
			FROM `' . $this->app->db->prefix . 'users`
			WHERE
				`username` = "Unit_Test"
			LIMIT 1
			;', FALSE);

		$user = isset($this->app->db->result[0]) ? $this->app->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Creating a user account in <code>/account</code>.',
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
				'auth-token'  => $this->app->input->authToken
				);

			$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->absPath . 'account/edit/' . ( int ) $user['id'], $post);
		}

		$this->app->db->sql('
			SELECT
				`email`
			FROM `' . $this->app->db->prefix . 'users`
			WHERE
				`id` = ' . ( int ) $user['id'] . '
			LIMIT 1
			;', FALSE);

		$email = isset($this->app->db->result[0]) ? $this->app->db->result[0]['email'] : FALSE;

		$params[] = array(
			'test' => 'Editing a user account in <code>/account</code>.',
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
				'auth-token' => $this->app->input->authToken
				);

			$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->absPath . 'account/delete/' . ( int ) $user['id'], $post);
		}

		$this->app->db->sql('
			SELECT
				`id`
			FROM `' . $this->app->db->prefix . 'users`
			WHERE
				`id` = ' . ( int ) $user['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a user account <code>/account</code>.',
			'pass' => !$this->app->db->result
			);

		/**
		 * Creating a user preference
		 */
		$this->app->user->save_pref(array(
			'pref'    => 'Unit Test',
			'type'    => 'text',
			'match'   => '/.* /'
			));

		$this->app->db->sql('
			SELECT
				`id`
			FROM `' . $this->app->db->prefix . 'user_prefs`
			WHERE
				`pref` = "Unit Test"
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Creating a user preference.',
			'pass' => $this->app->db->result
			);

		/**
		 * Deleting a user preference
		 */
		$this->app->user->delete_pref('Unit Test');

		$this->app->db->sql('
			SELECT
				`id`
			FROM `' . $this->app->db->prefix . 'user_prefs`
			WHERE
				`pref` = "Unit Test"
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a user preference.',
			'pass' => !$this->app->db->result
			);
	}
}

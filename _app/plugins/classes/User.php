<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->app) ) die('Direct access to this file is not allowed');

/**
 * User
 * @abstract
 */
class User
{
	public
		$ready,
		$prefs = array()
		;

	const
		GUEST_ID = 0
		;

	private
		$app,
		$view,
		$controller
		;

	/**
	 * Initialize
	 * @param object $this->app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		if ( !empty($app->db->ready) )
		{
			/**
			 * Check if the users table exists
			 */
			if ( in_array($app->db->prefix . 'users', $app->db->tables) )
			{
				$this->ready = TRUE;

				if ( in_array($app->db->prefix . 'user_prefs', $app->db->tables) )
				{
					$app->db->sql('
						SELECT
							*
						FROM `' . $app->db->prefix . 'user_prefs' . '`
						;');

					if ( $r = $app->db->result )
					{
						foreach ( $r as $d )
						{
							$this->prefs[$d['pref']] = $d;

							$this->prefs[$d['pref']]['options'] = unserialize($d['options']);
						}
					}
				}

				$app->session->put('pref_values', $this->get_pref_values($app->session->get('user id')));

				/**
				 * Guest user
				 */
				if ( $app->session->get('user id') === FALSE )
				{
					$app->session->put(array(
						'user id'       => User::GUEST_ID,
						'user username' => User::GUEST_ID
						));
				}
			}
		}
	}

	/**
	 * Login
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	function login($username, $password)
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
					$this->app->session->put(array(
						'user id'       => $r['id'],
						'user username' => $r['username'],
						'user email'    => $r['email'],
						'user is owner' => $r['owner']
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
		$this->app = $this->app;

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
		$this->app = $this->app;

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

		// Let's slow things down by hashing it a bunch of times
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
		$this->app = $this->app;

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
		$this->app = $this->app;

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
		$this->app = $this->app;

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
}

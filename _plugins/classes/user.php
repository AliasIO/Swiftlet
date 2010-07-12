<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->model) ) die('Direct access to this file is not allowed');

/**
 * Authorisation
 * @abstract
 */
class user
{
	public
		$ready,
		$prefs = array()
		;

	const
		guestId = 0
		;

	private
		$model,
		$view,
		$contr
		;

	/**
	 * Initialize authorisation
	 * @param object $this->model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->view  = $model->view;
		$this->contr = $model->contr;

		if ( !empty($model->db->ready) )
		{
			/**
			 * Check if the users table exists
			 */
			if ( in_array($model->db->prefix . 'users', $model->db->tables) )
			{
				$this->ready = TRUE;

				if ( in_array($model->db->prefix . 'user_prefs', $model->db->tables) )
				{
					$model->db->sql('
						SELECT
							*
						FROM `' . $model->db->prefix . 'user_prefs' . '`
						;');

					if ( $r = $model->db->result )
					{
						foreach ( $r as $d )
						{
							$this->prefs[$d['pref']] = $d;

							$this->prefs[$d['pref']]['options'] = unserialize($d['options']);
						}
					}
				}

				$model->session->put('pref_values', $this->get_pref_values($model->session->get('user id')));

				/**
				 * Guest user
				 */
				if ( $model->session->get('user id') === FALSE )
				{
					$model->session->put(array(
						'user id'       => user::guestId,
						'user username' => user::guestId
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
		if ( $this->model->session->get('user id') !== FALSE )
		{
			$this->model->db->sql('
				UPDATE `' . $this->model->db->prefix . 'users` SET
					`date_login_attempt` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`username` = "' . $this->model->db->escape($username) . '"
				LIMIT 1
				;');

			if ( $this->validate_password($username, $password) )
			{
				$this->model->db->sql('
					SELECT
						*
					FROM `' . $this->model->db->prefix . 'users`
					WHERE
						`username` = "' . $this->model->db->escape($username) . '"
					LIMIT 1
					;', FALSE);

				if ( !empty($this->model->db->result[0]) && $r = $this->model->db->result[0] )
				{
					$this->model->session->put(array(
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
		$this->model = $this->model;

		$this->model->session->reset();
		$this->model->session->end();
	}

	/**
 	 * Validate password
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	function validate_password($username, $password)
	{
		$this->model = $this->model;

		$this->model->db->sql('
			SELECT
				`pass_hash`
			FROM `' . $this->model->db->prefix . 'users`
			WHERE
				`username` = "' . $this->model->db->escape($username) . '"
			LIMIT 1
			;', FALSE);

		if ( !empty($this->model->db->result[0]) && $r = $this->model->db->result[0] )
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

				$this->model->db->sql('
					UPDATE `' . $this->model->db->prefix . 'users` SET
						`pass_hash` = "' . $passHash . '"
					WHERE
						`username` = "' . $this->model->db->escape($username) . '"
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

		$this->model->db->sql('
			INSERT INTO `' . $this->model->db->prefix . 'user_prefs` (
				`pref`,
				`type`,
				`match`,
				`options`
				)
			VALUES (
				"' . $this->model->db->escape($params['pref'])    . '",
				"' . $this->model->db->escape($params['type'])    . '",
				"' . $this->model->db->escape($params['match'])   . '",
				"' . $this->model->db->escape(serialize($params['options'])) . '"
				)
			ON DUPLICATE KEY UPDATE
				`options` = "' . $this->model->db->escape(serialize($params['options'])) . '"
			;');
	}

	/**
	 * Delete a preference
	 * @param string $pref
	 */
	function delete_pref($pref)
	{
		$this->model = $this->model;

		$this->model->db->sql('
			DELETE
				up, upx
			FROM      `' . $this->model->db->prefix . 'user_prefs`      AS up
			LEFT JOIN `' . $this->model->db->prefix . 'user_prefs_xref` AS upx ON up.`id` = upx.`pref_id`
			WHERE
				up.`pref` = "' . $this->model->db->escape($pref) . '"
			;');
	}

	/**
	 * Save a preference value
	 * @param array $params
	 */
	function save_pref_value($params)
	{
		$this->model = $this->model;

		$this->model->db->sql('
			INSERT INTO `' . $this->model->db->prefix . 'user_prefs_xref` (
				`user_id`,
				`pref_id`,
				`value`
				)
			VALUES (
				' . ( int ) $params['user_id'] . ',
				' . ( int ) $this->prefs[$params['pref']]['id'] . ',
				"' . $this->model->db->escape($params['value']) . '"
				)
			ON DUPLICATE KEY UPDATE
				`value` = "' . $this->model->db->escape($params['value']) . '"
			;');

		if ( $this->model->db->result )
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
		$this->model = $this->model;

		$prefs = array();

		if ( ( int ) $userId )
		{
			$this->model->db->sql('
				SELECT
					uo.`pref`,
					uox.`value`
				FROM      `' . $this->model->db->prefix . 'user_prefs`      AS uo
				LEFT JOIN `' . $this->model->db->prefix . 'user_prefs_xref` AS uox ON uo.`id` = uox.`pref_id`
				WHERE
					uox.`user_id` = ' . ( int ) $userId . '
				;');

			if ( $r = $this->model->db->result )
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

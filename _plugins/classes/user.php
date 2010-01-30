<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

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
		$contr
		;

	/**
	 * Initialize authorisation
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
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
		$model = $this->model;

		if ( $model->session->get('user id') !== FALSE )
		{
			$model->db->sql('
				UPDATE `' . $model->db->prefix . 'users` SET
					`date_login_attempt` = "' . gmdate('Y-m-d H:i:s') . '"
				WHERE
					`username` = "' . $model->db->escape($username) . '"
				LIMIT 1
				;');

			if ( $this->validate_password($username, $password) )
			{
				$model->db->sql('
					SELECT
						*
					FROM `' . $model->db->prefix . 'users`
					WHERE
						`username` = "' . $model->db->escape($username) . '"
					LIMIT 1
					;', FALSE);

				if ( !empty($model->db->result[0]) && $r = $model->db->result[0] )
				{
					$model->session->put(array(
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
		$model = $this->model;
		
		$model->session->reset();
		$model->session->end();
	}

	/**
 	 * Validate password
	 */
	function validate_password($username, $password)
	{
		$model = $this->model;

		$model->db->sql('
			SELECT
				`salt`,
				`pass_hash`
			FROM `' . $model->db->prefix . 'users`
			WHERE
				`username` = "' . $model->db->escape($username) . '"
			LIMIT 1
			;', FALSE);

		if ( !empty($model->db->result[0]) && $r = $model->db->result[0] )
		{
			$passHash = hash('sha256', 'swiftlet' . $r['salt'] . strtolower($username) . $password);

			if ( $passHash == $r['pass_hash'] )
			{
				return true;
			}
		}
	}

	/**
	 * Save a preference
	 * @param array $params
	 */
	function save_pref($params)
	{
		$model = $this->model;
		
		$model->db->sql('
			INSERT INTO `' . $model->db->prefix . 'user_prefs` (
				`pref`,
				`type`,
				`match`,
				`options`
				)
			VALUES (
				"' . $model->db->escape($params['pref'])    . '",
				"' . $model->db->escape($params['type'])    . '",
				"' . $model->db->escape($params['match'])   . '",
				"' . $model->db->escape($params['options']) . '"
				)
			ON DUPLICATE KEY UPDATE
				`options` = "' . $model->db->escape($params['options']) . '"
			;');
	}

	/**
	 * Delete a preference
	 * @param string $pref
	 */
	function delete_pref($pref)
	{
		$model = $this->model;

		$model->db->sql('
			DELETE
				up, upx
			FROM      `' . $model->db->prefix . 'user_prefs`      AS up
			LEFT JOIN `' . $model->db->prefix . 'user_prefs_xref` AS upx ON up.`id` = upx.`pref_id`
			WHERE
				up.`pref` = "' . $model->db->escape($pref) . '"
			;');
	}

	/**
	 * Save a preference value
	 * @param array $params
	 */
	function save_pref_value($params)
	{
		$model = $this->model;

		$model->db->sql('
			INSERT INTO `' . $model->db->prefix . 'user_prefs_xref` (
				`user_id`,
				`pref_id`,
				`value`
				)
			VALUES (
				' . ( int ) $params['user_id'] . ',
				' . ( int ) $this->prefs[$params['pref']]['id'] . ',
				"' . $model->db->escape($params['value']) . '"
				)
			ON DUPLICATE KEY UPDATE
				`value` = "' . $model->db->escape($params['value']) . '"
			;');

		if ( $model->db->result )
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
		$model = $this->model;
		
		$prefs = array();

		if ( ( int ) $userId )
		{
			$model->db->sql('
				SELECT
					uo.`pref`,
					uox.`value`
				FROM      `' . $model->db->prefix . 'user_prefs`      AS uo
				LEFT JOIN `' . $model->db->prefix . 'user_prefs_xref` AS uox ON uo.`id` = uox.`pref_id`
				WHERE
					uox.`user_id` = ' . ( int ) $userId . '
				;');

			if ( $r = $model->db->result )
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

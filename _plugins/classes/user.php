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
		guestId = 0,

		banned  = -1,
		guest   = 0,
		user    = 1,
		editor  = 2,
		admin   = 3,
		owner   = 4,
		dev     = 5
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

							$this->prefs[$d['pref']]['values'] = unserialize($d['values']);
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
						'user username' => user::guestId,
						'user auth'     => user::guest
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
			$pass_hash = sha1('swiftlet' . strtolower($username) . $password);

			$model->db->sql('
				SELECT
					*
				FROM `' . $model->db->prefix . 'users`
				WHERE
					`username`  = "' . $model->db->escape($username)  . '" AND
					`pass_hash` = "' . $model->db->escape($pass_hash) . '"
				LIMIT 1
				;', FALSE);

			if ( isset($model->db->result[0]) && $r = $model->db->result[0] )
			{
				$model->session->put(array(
					'user id'       => $r['id'],
					'user username' => $r['username'],
					'user auth'     => $r['auth'],
					'user email'    => $r['email']
					));
				
				return TRUE;
			}

			return FALSE;
		}

		return FALSE;
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
	 * Save a new preference
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
				`values`
				)
			VALUES (
				"' . $model->db->escape($params['pref']) . '",
				"' . $model->db->escape($params['type'])   . '",
				"' . $model->db->escape($params['match'])  . '",
				"' . $model->db->escape($params['values']) . '"
				)
			ON DUPLICATE KEY UPDATE
				`values` = "' . $model->db->escape($params['values']) . '"
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
				uo, uox
			FROM      `' . $model->db->prefix . 'user_prefs`      AS uo
			LEFT JOIN `' . $model->db->prefix . 'user_prefs_xref` AS uox ON uo.`id` = uox.`pref_id`
			WHERE
				uo.`pref` = "' . $model->db->escape($pref) . '"
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
	 * Get preferences
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
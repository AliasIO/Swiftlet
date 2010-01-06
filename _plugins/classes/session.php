<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Session
 * @abstract
 */
class session
{
	public
		$ready,
		$id,
		$lifeTime = 3600,
		$contents = array()
		;

	private
		$model,
		$contr,

		$hash
		;

	/**
	 * Initialize session
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;

		/**
		 * Check if the sessions table exists
		 */
		if ( in_array($model->db->prefix . 'sessions', $model->db->tables) )
		{
			$this->ready = TRUE;

			$this->id = !empty($_COOKIE['sw_session']) ? ( int ) $_COOKIE['sw_session'] : FALSE;

			$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

			$this->hash = sha1($model->userIp . $userAgent . $_SERVER['SERVER_ADDR'] . $this->contr->absPath);

			/**
			 * Delete expired sessions
			 */
			$this->model->db->sql('
				DELETE
				FROM `' . $this->model->db->prefix . 'sessions`
				WHERE
					`date_expire` <= NOW()
				;');

			/**
			 * Get session contents
			 */
			if ( $this->id )
			{
				$model->db->sql('
					SELECT
						`contents`
					FROM `' . $model->db->prefix . 'sessions`
					WHERE
						`id`   = '  . $this->id   . ' AND
						`hash` = "' . $this->hash . '"
					LIMIT 1
					;', FALSE);

				if ( $r = $model->db->result )
				{
					$this->contents = unserialize($r[0]['contents']);

					if ( !is_array($this->contents) )
					{
						$this->contents = array($this->contents);
					}
				}
			}

			if ( !$this->id || !$r )
			{
				/**
				 * Create a new sessions if no session exists
				 */
				$model->db->sql('
					INSERT
					INTO `' . $model->db->prefix . 'sessions` (
						`hash`,
						`contents`,
						`date`,
						`date_expire`
						)
					VALUES (
						"' . $this->hash . '",
						"' . $model->db->escape(serialize($this->contents)) . '",
						NOW(),
						DATE_ADD(NOW(), INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
						)
					ON DUPLICATE KEY UPDATE
						`contents`    = "' . $model->db->escape(serialize($this->contents)) . '",
						`date`        = NOW(),
						`date_expire` = DATE_ADD(NOW(), INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
					;');

				$this->id = $model->db->result;
			}
		}
	}

	/**
	 * Get a session variable
	 * @param string $k
	 * @param mixed $default
	 * @return string
	 */
	function get($k, $default = FALSE)
	{
		return isset($this->contents[$k]) ? $this->contents[$k] : $default;
	}

	/**
	 * Set a session variable
	 * @param mixed $k
	 * @param string $v
	 */
	function put($k, $v = FALSE)
	{
		if ( is_array($k) )
		{
			foreach ( $k as $k2 => $v2 )
			{
				$this->contents[$k2] = $v2;
			}
		}
		else
		{
			$this->contents[$k] = $v;
		}
	}

	/**
	 * Clear session variables
	 */
	function reset()
	{
		$this->contents = array();
	}

	/**
	 * Save session contents
	 */
	function end()
	{
		$this->model->db->sql('
			UPDATE `' . $this->model->db->prefix . 'sessions`
			SET
				`contents` = "' . $this->model->db->escape(serialize($this->contents)) . '",
				`date_expire` = DATE_ADD(NOW(), INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
			WHERE
				`id` = ' . $this->id . '
			LIMIT 1						
			;');

		setcookie('sw_session', $this->id, time() + $this->lifeTime, $this->contr->absPath);
	}
}
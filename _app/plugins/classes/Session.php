<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->app) ) die('Direct access to this file is not allowed');

/**
 * Session
 * @abstract
 */
class Session
{
	public
		$ready,
		$id,
		$lifeTime = 3600,
		$contents = array()
		;

	private
		$app,
		$controller,

		$hash
		;

	/**
	 * Initialize session
	 * @param object $this->app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		/**
		 * Check if the sessions table exists
		 */
		if ( in_array($app->db->prefix . 'sessions', $app->db->tables) )
		{
			$this->ready = TRUE;

			$this->id = !empty($_COOKIE['sw_session']) ? ( int ) $_COOKIE['sw_session'] : FALSE;

			$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

			$this->hash = sha1($this->app->userIp . $userAgent . $_SERVER['SERVER_NAME'] . $this->controller->absPath);

			/**
			 * Delete expired sessions
			 */
			$app->db->sql('
				DELETE
				FROM `' . $this->app->db->prefix . 'sessions`
				WHERE
					`date_expire` <= "' . gmdate('Y-m-d H:i:s') . '"
				;');

			/**
			 * Get session contents
			 */
			if ( $this->id )
			{
				$app->db->sql('
					SELECT
						`contents`
					FROM `' . $this->app->db->prefix . 'sessions`
					WHERE
						`id`   =  ' . $this->id   . ' AND
						`hash` = "' . $this->hash . '"
					LIMIT 1
					;', FALSE);

				if ( $r = $app->db->result )
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
				$app->db->sql('
					INSERT
					INTO `' . $app->db->prefix . 'sessions` (
						`hash`,
						`contents`,
						`date`,
						`date_expire`
						)
					VALUES (
						"' . $this->hash                                  . '",
						"' . $app->db->escape(serialize($this->contents)) . '",
						"' . gmdate('Y-m-d H:i:s')                        . '",
						DATE_ADD("' . gmdate('Y-m-d H:i:s') . '", INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
						)
					ON DUPLICATE KEY UPDATE
						`contents`    = "' . $app->db->escape(serialize($this->contents)) . '",
						`date`        = "' . gmdate('Y-m-d H:i:s')                        . '",
						`date_expire` = DATE_ADD("' . gmdate('Y-m-d H:i:s') . '", INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
					;');

				$this->id = $app->db->result;
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
		if ( in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
		{
			$this->app->db->sql('
				UPDATE `' . $this->app->db->prefix . 'sessions`
				SET
					`contents`    = "' . $this->app->db->escape(serialize($this->contents)) . '",
					`date_expire` = DATE_ADD("' . gmdate('Y-m-d H:i:s') . '", INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
				WHERE
					`id` = ' . $this->id . '
				LIMIT 1
				;');
		}

		setcookie('sw_session', $this->id, time() + $this->lifeTime, $this->controller->absPath);
	}
}

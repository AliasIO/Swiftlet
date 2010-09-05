<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Session_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db'),
		$hooks        = array('init' => 2, 'install' => 1, 'end' => 1, 'remove' => 1),

		$id,
		$lifeTime = 3600,
		$contents = array()
		;

	private
		$hash
		;

	function install()
	{
		if ( !in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'sessions` (
					`id`          INT(10)     UNSIGNED NOT NULL AUTO_INCREMENT,
					`hash`        VARCHAR(40)          NOT NULL,
					`contents`    TEXT                     NULL,
					`date`        DATETIME             NOT NULL,
					`date_expire` DATETIME             NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `hash` (`hash`)
					) TYPE = INNODB
				;');
		}
	}

	function remove()
	{
		if ( in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE `' . $this->app->db->prefix . 'sessions`;');
		}
	}

	function init()
	{
		/**
		 * Check if the sessions table exists
		 */
		if ( in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
		{
			$this->ready = TRUE;

			$this->id = !empty($_COOKIE['sw_session']) ? ( int ) $_COOKIE['sw_session'] : FALSE;

			$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

			$this->hash = sha1($this->app->userIp . $userAgent . $_SERVER['SERVER_NAME']);// . $this->controller->absPath);

			/**
			 * Delete expired sessions
			 */
			$this->app->db->sql('
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
				$this->app->db->sql('
					SELECT
						`contents`
					FROM `' . $this->app->db->prefix . 'sessions`
					WHERE
						`id`   =  ' . $this->id   . ' AND
						`hash` = "' . $this->hash . '"
					LIMIT 1
					;', FALSE);

				if ( $r = $this->app->db->result )
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
				$this->app->db->sql('
					INSERT
					INTO `' . $this->app->db->prefix . 'sessions` (
						`hash`,
						`contents`,
						`date`,
						`date_expire`
						)
					VALUES (
						"' . $this->hash                                        . '",
						"' . $this->app->db->escape(serialize($this->contents)) . '",
						"' . gmdate('Y-m-d H:i:s')                              . '",
						DATE_ADD("' . gmdate('Y-m-d H:i:s') . '", INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
						)
					ON DUPLICATE KEY UPDATE
						`contents`    = "' . $this->app->db->escape(serialize($this->contents)) . '",
						`date`        = "' . gmdate('Y-m-d H:i:s')                              . '",
						`date_expire` = DATE_ADD("' . gmdate('Y-m-d H:i:s') . '", INTERVAL ' . ( int ) $this->lifeTime . ' SECOND)
					;');

				$this->id = $this->app->db->result;
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

		setcookie('sw_session', $this->id, time() + $this->lifeTime);//, $this->controller->absPath);
	}
}

<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Session_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db'),
		$hooks        = array('init' => 2, 'install' => 1, 'end' => 1, 'remove' => 1)
		;

	public
		$id              = FALSE,
		$sessionLifeTime = 3600,
		$contents        = array()
		;

	private
		$hash,
		$key
		;

	/*
	 * Implement install hook
	 */
	function install()
	{
		if ( !in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE {sessions} (
					`id`          INT(10)     UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					`hash`        VARCHAR(40)          NOT NULL UNIQUE,
					`contents`    TEXT                     NULL,
					`date`        DATETIME             NOT NULL,
					`date_expire` DATETIME             NOT NULL
					) ENGINE = INNODB
				');
		}
	}

	/*
	 * Implement remove hook
	 */
	function remove()
	{
		if ( in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
		{
			$this->app->db->sql('DROP TABLE {sessions}');
		}
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

		$this->hash = sha1($this->app->userIp . $userAgent . $_SERVER['SERVER_NAME'] . $this->view->absPath . $this->key);

		if ( !empty($_COOKIE['sw_session']) && strstr($_COOKIE['sw_session'], ':') )
		{
			list($this->id, $this->key) = explode(':', $_COOKIE['sw_session']);

			if ( !$this->key )
			{
				$this->key = sha1(uniqid(mt_rand(), TRUE));
			}

			/**
			 * Delete expired sessions
			 */
			$this->app->db->sql('
				DELETE
				FROM {sessions}
				WHERE
					`date_expire` <= :date
				', array(
					':date' => gmdate('Y-m-d H:i:s')
					)
				);

			/**
			 * Get session contents
			 */
			if ( $this->id )
			{
				$this->app->db->sql('
					SELECT
						`contents`
					FROM {sessions}
					WHERE
						`id`   = :id   AND
						`hash` = :hash
					LIMIT 1
					', array(
						':id'   => $this->id,
						':hash' => $this->hash
						), FALSE
					);

				if ( $r = $this->app->db->result )
				{
					$this->contents = unserialize($r[0]['contents']);

					if ( !is_array($this->contents) )
					{
						$this->contents = array($this->contents);
					}
				}
				else
				{
					$this->id = FALSE;
				}
			}
		}
	}

	/**
	 * Create a new session
	 */
	function create()
	{
		if ( !$this->id )
		{
			$this->app->db->sql('
				INSERT
				INTO {sessions} (
					`hash`,
					`contents`,
					`date`,
					`date_expire`
					)
				VALUES (
					:hash,
					:contents,
					:date,
					DATE_ADD(:date, INTERVAL :session_life_time SECOND)
					)
				ON DUPLICATE KEY UPDATE
					`contents`    = :contents,
					`date`        = :date,
					`date_expire` = DATE_ADD(:date, INTERVAL :session_life_time SECOND)
				', array(
					':hash'              => $this->hash,
					':contents'          => serialize($this->contents),
					':date'              => gmdate('Y-m-d H:i:s'),
					':session_life_time' => ( int ) $this->sessionLifeTime
					)
				);

			$this->id = $this->app->db->result;
		}
	}

	/**
	 * End session
	 */
	function destroy()
	{
		if ( $this->id )
		{
			$this->app->db->sql('
				DELETE
				FROM {sessions}
				WHERE
					`id` = :id
				LIMIT 1
				', array(
					':id' => ( int ) $id
					)
				);

			$this->id = FALSE;
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
	 * Implement end hook
	 * Save session contents
	 */
	function end()
	{
		if ( $this->id )
		{
			$sessionLifeTime = ( int ) !empty($this->contents['lifetime']) ? $this->contents['lifetime'] : $this->sessionLifeTime;

			if ( in_array($this->app->db->prefix . 'sessions', $this->app->db->tables) )
			{
				$this->app->db->sql('
					UPDATE {sessions}
					SET
						`contents`    = :contents,
						`date_expire` = DATE_ADD(:date, INTERVAL :session_life_time SECOND)
					WHERE
						`id` = :id
					LIMIT 1
					', array(
						':contents'          => serialize($this->contents),
						':date'              => gmdate('Y-m-d H:i:s'),
						':session_life_time' => ( int ) $sessionLifeTime,
						':id'                => $this->id
						)
					);
			}

			$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? TRUE : FALSE;

			// Using FALSE for hostname, $_SERVER['SERVER_NAME'] doesn't work on WAMP
			setcookie('sw_session', $this->id . ':' . $this->key, time() + $sessionLifeTime, $this->view->absPath, FALSE, $secure, TRUE);
		}
	}
}

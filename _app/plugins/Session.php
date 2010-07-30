<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Session extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.2.0', 'to' => '1.2.*'),
		$dependencies = array('db'),
		$hooks        = array('init' => 2, 'install' => 1, 'end' => 1, 'remove' => 1),

		$id,
		$lifeTime     = 3600,
		$contents     = array()
		;

	private
		$hash
		;

	function hook_install()
	{
		if ( !in_array($app->db->prefix . 'sessions', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'sessions` (
					`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`hash`        VARCHAR(40)      NOT NULL,
					`contents`    TEXT             NULL,
					`date`        DATETIME         NOT NULL,
					`date_expire` DATETIME         NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `hash` (`hash`)
					) TYPE = INNODB
				;');
		}
	}

	function hook_remove()
	{
		if ( in_array($app->db->prefix . 'sessions', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'sessions`;');
		}
	}

	function hook_init()
	{
		$this->ready = TRUE;
	}

	function hook_end()
	{
		if ( !empty($app->session->ready) )
		{
			$app->session->end();
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

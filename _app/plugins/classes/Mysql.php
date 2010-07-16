<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * MySQL database
 * @abstract
 */
class Mysql
{
	private
		$app,
		$view,
		$controller
		;

	public
		$link,
		$ready,
		$prefix,
		$tables = array()
		;

	/**
	 * Initialize database connection
	 * @param object $app
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $name
	 * @param string $prefix
	 */
	function __construct($app, $host, $user, $pass = FALSE, $name = FALSE, $prefix = FALSE)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		$this->prefix = $prefix;

		$app->debugOutput['mysql queries'] = array('reads' => 0, 'writes' => 0);

		if ( $name )
		{
			$this->link = mysql_connect($host, $user, $pass)
				or $app->error(mysql_errno(), mysql_error(), __FILE__, __LINE__);

			if ( is_resource($this->link) )
			{
				mysql_select_db($name, $this->link)
					or $app->error(mysql_errno(), mysql_error(), __FILE__, __LINE__);

				$this->ready = TRUE;

				$this->sql('SHOW TABLES;');

				if ( $r = $this->result )
				{
					foreach ( $r as $d )
					{
						$this->tables[$d[0]] = $d[0];
					}
				}

				/**
				 * Check if the cache tables exists
				 */
				if ( in_array($this->prefix . 'cache_queries', $this->tables) && in_array($this->prefix . 'cache_tables', $this->tables) )
				{
					/**
					 * Clear cache
					 */
					$this->sql('
						DELETE
							cq, ct
						FROM      `' . $this->prefix . 'cache_queries` AS cq
						LEFT JOIN `' . $this->prefix . 'cache_tables`  AS ct ON cq.`id` = ct.`query_id`
						WHERE
							cq.`date_expire` <= "' . gmdate('Y-m-d H:i:s') . '"
						;');
				}
				else
				{
					$app->caching = FALSE;
				}
			}
		}
	}

	/**
	 * Perform a MySQL query
	 * @param string $sql
	 */
	function sql($sql, $cache = TRUE)
	{
		if ( !$this->ready )
		{
			$this->app->error(FALSE, 'No database connection (SQL: ' . $sql . ')', __FILE__, __LINE__);
		}

		$this->result = array();

		$this->sql = trim(preg_replace('/\s+/', ' ', $sql));

		switch ( TRUE )
		{
			case preg_match('/^(SELECT|SHOW|EXPLAIN)/i', $this->sql):
				$this->read($cache);

				return $this->result;

				break;
			case preg_match('/^INSERT/i', $this->sql):
				$this->write();

				return $this->result = mysql_insert_id();

				break;
			case preg_match('/^DROP TABLE/i', $this->sql):
				$this->write();

				$tables = $this->get_tables();

				foreach ( $tables as $table )
				{
					unset($this->tables[$table]);
				}

				return $this->result = mysql_insert_id();

				break;
			default:
				$this->write();

				return $this->result = mysql_affected_rows();

				break;
		}
	}

	/**
	 * Perform a MySQL read query
	 */
	private function read($cache)
	{
		$this->app->debugOutput['mysql queries']['reads'] ++;

		$tables = $this->get_tables();

		/**
		 * Check if the query is cached
		 */
		$hash = sha1($this->sql);

		if ( $this->app->caching && $cache && $tables )
		{
			$sql = $this->sql;

			$this->sql('
				SELECT
					`results`
				FROM      `' . $this->prefix . 'cache_queries` AS cq
				LEFT JOIN `' . $this->prefix . 'cache_tables`  AS ct ON cq.`id` = ct.`query_id`
				WHERE
					cq.`hash` = "' . $this->escape($hash) . '" AND
					ct.`table` IN ( "' . implode('", "', $tables) . '" )
				LIMIT 1
				;', FALSE);

			if ( $this->result && $r = $this->result[0] )
			{
				$this->result = @unserialize($r['results']);

				if ( $this->result )
				{
					return;
				}
				else
				{
					// Remove invalid cached result
					$this->sql('
						DELETE
							cq, ct
						FROM      `' . $this->prefix . 'cache_queries` AS cq
						LEFT JOIN `' . $this->prefix . 'cache_tables`  AS ct ON cq.`id` = ct.`query_id`
						WHERE
							cq.`hash` = "' . $this->escape($hash) . '"
						;');
				}
			}

			$this->sql    = $sql;
			$this->result = array();
		}

		/**
		 * Not cached, execute query
		 */
		$timerStart = $this->app->timer_start();

		$r = mysql_query($this->sql)
			or $this->app->error(mysql_errno(), mysql_error() . '<pre>' . $this->sql . '</pre>', __FILE__, __LINE__);

		$timerEnd = $this->app->timer_end($timerStart);

		if ( $r )
		{
			while ( $d = mysql_fetch_array($r) )
			{
				$this->result[] = $d;
			}
		}

		if ( $this->app->debugMode && preg_match('/^SELECT/i', $this->sql) )
		{
			/**
			 * Get detailed debug information about a SELECT query
			 */
			$r = mysql_query('EXPLAIN ' . $this->sql);

			$this->app->debugOutput['mysql queries'][] = array('sql' => $this->sql, 'execution time' => $timerEnd, 'explain' => mysql_fetch_assoc($r));
		}
		else
		{
			$this->app->debugOutput['mysql queries'][] = array('sql' => $this->sql, 'execution time' => $timerEnd);
		}

		mysql_free_result($r);

		/**
		 * Cache results
		 */
		if ( $this->result && $this->app->caching && $cache && $tables )
		{
			$result = $this->result;

			if ( in_array($this->prefix . 'cache_queries', $this->tables) && in_array($this->prefix . 'cache_tables', $this->tables) )
			{
				$this->sql('
					INSERT INTO `' . $this->prefix . 'cache_queries` (
						`hash`,
						`results`,
						`date`,
						`date_expire`
						)
					VALUES (
						"' . $this->escape($hash)              . '",
						"' . $this->escape(serialize($result)) . '",
						"' . gmdate('Y-m-d H:i:s')             . '",
						DATE_ADD("' . gmdate('Y-m-d H:i:s') . '", INTERVAL 1 HOUR)
						)
					;');

				if ( $id = $this->result )
				{
					foreach ( $tables as $table )
					{
						$this->sql('
							INSERT INTO `' . $this->prefix . 'cache_tables` (
								`query_id`,
								`table`
								)
							VALUES (
								 ' . ( int ) $id           . ',
								"' . $this->escape($table) . '"
								)
							;');
					}
				}
			}

			$this->result = $result;
		}
	}

	/**
	 * Perform a MySQL write query
	 */
	private function write()
	{
		$this->app->debugOutput['mysql queries']['writes'] ++;

		/**
		 * Clear cache
		 */
		$tables = $this->get_tables();

		if ( $this->app->caching && $tables )
		{
			$sql = $this->sql;

			$this->sql('
				DELETE
					cq, ct
				FROM      `' . $this->prefix . 'cache_queries` AS cq
				LEFT JOIN `' . $this->prefix . 'cache_tables`  AS ct ON cq.`id` = ct.`query_id`
				WHERE
					ct.`table` IN ( "' . implode('", "', $tables) . '" )
				;');

			$this->sql    = $sql;
			$this->result = array();
		}

		$timerStart = $this->app->timer_start();

		$r = mysql_query($this->sql)
			or $this->app->error(mysql_errno(), mysql_error() . '<pre>' . $this->sql . '</pre>', __FILE__, __LINE__);

		$this->app->debugOutput['mysql queries'][] = array('sql' => $this->sql, 'affected rows' => mysql_affected_rows(), 'execution time' => $this->app->timer_end($timerStart));
	}

	/**
	* Get table names from query
	* @param string $sql
	* @return array
	*/
	private function get_tables()
	{
		$tables = array();

		preg_match_all('/(FROM|JOIN|UPDATE|INTO|TRUNCATE|DROP TABLE) (`?(' . preg_quote($this->prefix, '/') . '[a-z0-9_]+)`?\s?,?)+/i', $this->sql, $m);

		if ( isset($m[3]) )
		{
			foreach ( $m[3] as $match )
			{
				if ( $match != $this->prefix . 'cache_queries' && $match != $this->prefix . 'cache_tables' )
				{
					$tables[] = $this->escape($match);
				}
			}
		}

		return $tables;
	}

	/**
	 * Close database connection
	 */
	function close()
	{
		if ( $this->ready )
		{
			mysql_close($this->link);

			$this->ready = FALSE;
		}
	}

	/**
	 * Escape values for safe database insertion
	 * @param mixed $v
	 * @return mixed $v
	 */
	function escape($v)
	{
		if ( $this->ready )
		{
			if ( is_array($v) )
			{
				return array_map(array($this, 'escape'), $v);
			}
			else
			{
				return mysql_real_escape_string($v);
			}
		}
	}

	/**
	 * Sanitize user input
	 * @params mixed $v
	 * @return mixed
	 */
	function sanitize($v)
	{
		if ( is_array($v) )
		{
			return array_map(array($this, 'sanitize'), $v);
		}
		else
		{
			return $this->escape($this->view->h($v));
		}
	}

	/**
	 * Clear cache
	 */
	function clear_cache()
	{
		if ( $this->ready )
		{
			$this->sql('
				TRUNCATE TABLE `' . $this->prefix . 'cache_queries`
				');

			$this->sql('
				TRUNCATE TABLE `' . $this->prefix . 'cache_queries`
				');
		}
	}
}

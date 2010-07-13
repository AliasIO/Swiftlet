<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'info':
		$info = array(
			'name'       => 'db',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('clear_cache' => 1, 'init' => 1, 'install' => 1, 'input_sanitize' => 1, 'end' => 999, 'remove' => 1)
			);

		break;
	case 'install':
		if ( !in_array($app->db->prefix . 'cache_queries', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'cache_queries` (
					`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`hash`        VARCHAR(40)      NOT NULL,
					`results`     TEXT             NOT NULL,
					`date`        DATETIME         NOT NULL,
					`date_expire` DATETIME         NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `hash` (`hash`),
					INDEX `date_expire` (`date_expire`)
					) TYPE = INNODB
				;');
		}

		if ( !in_array($app->db->prefix . 'cache_tables', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'cache_tables` (
					`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`query_id`    INT(10) UNSIGNED NOT NULL,
					`table`       VARCHAR(255)     NOT NULL,
					PRIMARY KEY (`id`),
					INDEX `query_id` (`query_id`),
					INDEX `table`    (`table`)
					) TYPE = INNODB
				;');
		}

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'cache_queries', $app->db->tables) )
		{
			unset($app->db->tables[$app->db->prefix . 'cache_queries']);

			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'cache_queries`;');
		}

		if ( in_array($app->db->prefix . 'cache_tables', $app->db->tables) )
		{
			unset($app->db->tables[$app->db->prefix . 'cache_tables']);

			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'cache_tables`;');
		}

		break;
	case 'init':
		require($controller->classPath . 'mysql.php');

		$app->db = new mysql($app, $app->dbHost, $app->dbUser, $app->dbPass, $app->dbName, $app->dbPrefix);

		break;
	case 'input_sanitize':
		if ( !empty($app->db->ready) )
		{
			$app->POST_db_safe = $app->db->sanitize($app->POST_raw);
			$app->GET_db_safe  = $app->db->sanitize($app->GET_raw);
		}

		break;
	case 'clear_cache':
		if ( !empty($app->db->ready) )
		{
			$app->db->clear_cache();
		}

		break;
	case 'end':
		$app->db->close();				

		break;
}

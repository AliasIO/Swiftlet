<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

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
		if ( !in_array($model->db->prefix . 'cache_queries', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'cache_queries` (
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

		if ( !in_array($model->db->prefix . 'cache_tables', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'cache_tables` (
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
		if ( in_array($model->db->prefix . 'cache_queries', $model->db->tables) )
		{
			unset($model->db->tables[$model->db->prefix . 'cache_queries']);

			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'cache_queries`;');
		}

		if ( in_array($model->db->prefix . 'cache_tables', $model->db->tables) )
		{
			unset($model->db->tables[$model->db->prefix . 'cache_tables']);

			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'cache_tables`;');
		}

		break;
	case 'init':
		require($contr->classPath . 'mysql.php');

		$model->db = new mysql($model, $model->dbHost, $model->dbUser, $model->dbPass, $model->dbName, $model->dbPrefix);

		break;
	case 'input_sanitize':
		if ( !empty($model->db->ready) )
		{
			$model->POST_db_safe = $model->db->sanitize($model->POST_raw);
			$model->GET_db_safe  = $model->db->sanitize($model->GET_raw);
		}

		break;
	case 'clear_cache':
		if ( !empty($model->db->ready) )
		{
			$model->db->clear_cache();
		}

		break;
	case 'end':
		$model->db->close();				

		break;
}

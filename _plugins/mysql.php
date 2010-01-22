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
			'hooks'      => array('init' => 1, 'install' => 1, 'input_sanitize' => 1, 'end' => 999, 'remove' => 1)
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
					)
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
					)
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
		foreach ( $model->POST_raw as $k => $v )
		{
			$model->POST_db_safe[$k] = $model->db->escape($v);
		}

		foreach ( $model->GET_raw as $k => $v )
		{
			$model->GET_db_safe[$k] = $model->db->escape($v);
		}

		break;
	case 'end':
		$model->db->close();				

		break;
}
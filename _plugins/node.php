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
			'name'         => 'node',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db'),
			'hooks'        => array('init' => 4, 'install' => 1, 'remove' => 1, 'route' => 1)
			);

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'nodes', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'nodes` (
					`id`         INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
					`left_id`    INT(10)    UNSIGNED NOT NULL,
					`right_id`   INT(10)    UNSIGNED NOT NULL,
					`type`       VARCHAR(255)        NOT NULL,
					`title`      VARCHAR(255)        NOT NULL,
					`home`       TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
					`path`       VARCHAR(255)        NOT NULL,
					`date`       DATETIME            NOT NULL,
					`date_edit`  DATETIME            NOT NULL,
					INDEX `left_id`    (`left_id`),
					INDEX `right_id`   (`right_id`),
					INDEX `type`       (`type`),
					INDEX `home`       (`home`),
					INDEX `path`       (`path`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');

			$model->db->sql('
				INSERT INTO `' . $model->db->prefix . 'nodes` (
					`left_id`,
					`right_id`,
					`type`,
					`title`,
					`date`,
					`date_edit`
					)
				VALUES (
					0,
					1,
					"root",
					"ROOT",
					"' . gmdate('Y-m-d H:i:s') . '",
					"' . gmdate('Y-m-d H:i:s') . '"
					)
				;');
		}

		break;
	case 'remove':
		if ( in_array($model->db->prefix . 'nodes', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'nodes`;');
		}

		break;
	case 'init':
		if ( !empty($model->db->ready) )
		{		
			require($contr->classPath . 'node.php');
			
			$model->node = new node($model);
		}

		break;
	case 'route':
		if ( !empty($model->node->ready) )
		{
			$params = $model->node->route($params);
		}
}

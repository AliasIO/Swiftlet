<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'load':
		$pluginVersion = '1.0.0';

		$compatible = array('from' => '1.2.0', 'to' => '1.2.*');

		$dependencies = array('db');

		$model->hook_register($plugin, array('init' => 4));

		break;	
	case 'install':
		if ( !in_array($model->db->prefix . 'nodes', $model->db->tables) )
		{
			$description = '';

			$sql = array('
				CREATE TABLE `' . $model->db->prefix . 'nodes` (
					`id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`left_id`   INT(10) UNSIGNED NOT NULL,
					`right_id`  INT(10) UNSIGNED NOT NULL,
					`title`     VARCHAR(255)     NOT NULL,
					`permalink` VARCHAR(255)     NOT NULL,
					`date`      DATETIME         NOT NULL,
					`date_edit` DATETIME         NOT NULL,
					INDEX `left_id`  (`left_id`),
					INDEX `right_id` (`right_id`),
					UNIQUE `permalink` (`permalink`),
					PRIMARY KEY (`id`)
					);
				', '
				INSERT INTO `' . $model->db->prefix . 'nodes` (
					`left_id`,
					`right_id`,
					`title`,
					`permalink`,
					`date`,
					`date_edit`
					)
				VALUES (
					0,
					5,
					"ROOT",
					"root",
					NOW(),
					NOW()
					), (
					1,
					2,
					"Pages",
					"pages",
					NOW(),
					NOW()
					), (
					3,
					4,
					"Uploads",
					"uploads",
					NOW(),
					NOW()
					);
				');
		}
		
		break;
	case 'init':
		if ( !empty($model->db->ready) )
		{		
			require($contr->classPath . 'node.php');
			
			$model->node = new node($model);
		}

		break;
}
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
			'hooks'        => array('init' => 4, 'install' => 1)
			);

		break;
	case 'install':
		$model->db->sql('
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
				)
			;');

		$model->db->sql('
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
				1,
				"ROOT",
				"root",
				"' . gmdate('Y-m-d H:i:s') . '",
				"' . gmdate('Y-m-d H:i:s') . '"
				)
			;');

		break;
	case 'init':
		if ( !empty($model->db->ready) )
		{		
			require($contr->classPath . 'node.php');
			
			$model->node = new node($model);
		}

		break;
}
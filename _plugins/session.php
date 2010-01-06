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

		$model->hook_register($plugin, array('init' => 2, 'end' => 1));

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'sessions', $model->db->tables) )
		{
			$sql = '
				CREATE TABLE `' . $model->db->prefix . 'sessions` (
					`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`hash`        VARCHAR(40)      NOT NULL,
					`contents`    TEXT             NOT NULL,
					`date`        DATETIME         NOT NULL,
					`date_expire` DATETIME         NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `hash` (`hash`)
					);
				';
		}

		break;
	case 'init':
		if ( !empty($model->db->ready) )
		{
			require($contr->classPath . 'session.php');

			$model->session = new session($model);
		}

		break;
	case 'end':
		if ( !empty($model->session->ready) )
		{
			$model->session->end();
		}

		break;
}
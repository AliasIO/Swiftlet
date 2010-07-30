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
			'name'         => 'session',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db'),
			'hooks'        => array('init' => 2, 'install' => 1, 'end' => 1, 'remove' => 1)
			);

		break;
	case 'install':
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

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'sessions', $app->db->tables) )
		{
			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'sessions`;');
		}

		break;
	case 'init':
		if ( !empty($app->db->ready) )
		{
			require($controller->classPath . 'Session.php');

			$app->session = new session($app);
		}

		break;
	case 'end':
		if ( !empty($app->session->ready) )
		{
			$app->session->end();
		}

		break;
}

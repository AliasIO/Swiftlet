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
			'name'         => 'file',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'node'),
			'hooks'        => array('admin' => 2, 'init' => 5, 'install' => 1, 'unit_tests' => 1, 'url_rewrite' => 1)
			);

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'files', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'files` (
					`id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					`node_id`   INT(10) UNSIGNED NOT NULL,
					`title`     VARCHAR(255)     NOT NULL,
					`extension` VARCHAR(255)     NULL,
					`file_hash` VARCHAR(40)      NOT NULL,
					`mime_type` VARCHAR(255)     NOT NULL,
					`width`     INT(10) UNSIGNED NOT NULL,
					`height`    INT(10) UNSIGNED NOT NULL,
					`size`      INT(10) UNSIGNED NOT NULL,
					`date`      DATETIME         NOT NULL,
					`date_edit` DATETIME         NOT NULL
					INDEX `node_id` (`node_id`)
					PRIMARY KEY (`id`)
					)
				;');
		}

		if ( !empty($model->node->ready) )
		{
			$model->node->create('Files', 'files', node::rootId);
		}

		break;
	case 'init':
		if ( !empty($model->db->ready) && !empty($model->node->ready) )
		{
			require($contr->classPath . 'file.php');

			$model->file = new file($model);
		}

		break;
	case 'admin':
		$params[] = array(
			'name'        => 'Files',
			'description' => 'Upload and manage files',
			'group'       => 'Content',
			'path'        => 'admin/files/',
			'auth'        => 3,
			'order'       => 1
			);

		break;
	case 'url_rewrite':
		if ( $model->page->ready && !empty($params['url']) )
		{
			$params['url'] = $model->file->rewrite($params['url']);
		}

		break;
	case 'unit_tests':

		break;
}
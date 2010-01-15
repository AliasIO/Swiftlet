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
			'dependencies' => array('db', 'node', 'perm'),
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
					`image`     INT(1)           NULL,
					`file_hash` VARCHAR(40)      NOT NULL,
					`mime_type` VARCHAR(255)     NOT NULL,
					`width`     INT(10) UNSIGNED NOT NULL,
					`height`    INT(10) UNSIGNED NOT NULL,
					`size`      INT(10) UNSIGNED NOT NULL,
					`date`      DATETIME         NOT NULL,
					`date_edit` DATETIME         NOT NULL,
					INDEX `node_id` (`node_id`),
					INDEX `image`   (`image`),
					PRIMARY KEY (`id`)
					)
				;');
		}

		if ( !empty($model->node->ready) )
		{
			$model->node->create('Files', 'files', node::rootId);
		}

		if ( !empty($model->perm->ready) )
		{
			$model->perm->create('admin file access', 'Access to file management');
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
			'perm'        => 'admin file access',
			'order'       => 2
			);

		break;
	case 'url_rewrite':
		if ( $model->page->ready && !empty($params['url']) )
		{
			$params['url'] = $model->file->rewrite($params['url']);
		}

		break;
	case 'unit_tests':
		/**
		 * Uploading a file
		 */
		$post = array(
			'title[0]'    => 'Unit Test File',
			'file[0]'     => '@' . $contr->rootPath . 'favicon.ico',
			'form-submit' => 'Submit',
			'auth_token'  => $model->authToken
			);

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'admin/files/', $post);

		$model->db->sql('
			SELECT
				f.*
			FROM      `' . $model->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $model->db->prefix . 'files` AS f ON n.`id` = f.`node_id`
			WHERE
				n.`title` = "Unit Test File"
			LIMIT 1
			;', FALSE);

		$file = isset($model->db->result[0]) ? $model->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Uploading a file in <code>/admin/files/</code>.',
			'pass' => ( bool ) $file['node_id']
			);

		/**
		 * Deleting a file
		 */
		if ( $file['node_id'] )
		{
			$post = array(
				'get_data'   => serialize(array(
					'id'     => ( int ) $file['node_id'],
					'action' => 'delete'
					)),
				'confirm'    => '1',
				'auth_token' => $model->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'admin/files/?id=' . ( int ) $file['node_id'] . '&action=delete', $post);
		}

		$model->db->sql('
			SELECT
				n.`id`
			FROM      `' . $model->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $model->db->prefix . 'files` AS f ON n.`id` = f.`node_id`
			WHERE
				f.`id` = ' . ( int ) $file['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a file in <code>/admin/files/</code>.',
			'pass' => !$model->db->result
			);

		break;
}
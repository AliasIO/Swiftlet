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
			'name'         => 'file',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'permission'),
			'hooks'        => array('dashboard' => 2, 'init' => 5, 'install' => 1, 'remove' => 1, 'route' => 1, 'unit_tests' => 1)
			);

		break;
	case 'install':
		if ( !in_array($app->db->prefix . 'files', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'files` (
					`id`        INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
					`node_id`   INT(10)    UNSIGNED NOT NULL,
					`title`     VARCHAR(255)        NOT NULL,
					`extension` VARCHAR(255)            NULL,
					`image`     TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
					`filename`  VARCHAR(40)         NOT NULL,
					`mime_type` VARCHAR(255)        NOT NULL,
					`width`     INT(10)    UNSIGNED     NULL,
					`height`    INT(10)    UNSIGNED     NULL,
					`size`      INT(10)    UNSIGNED     NULL,
					`date`      DATETIME            NOT NULL,
					`date_edit` DATETIME            NOT NULL,
					INDEX `node_id` (`node_id`),
					INDEX `image`   (`image`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

		if ( !empty($app->permission->ready) )
		{
			$app->permission->create('Files', 'admin file access', 'Manage files');
			$app->permission->create('Files', 'admin file upload', 'Upload files');
			$app->permission->create('Files', 'admin file delete', 'Delete files');
		}

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'files', $app->db->tables) )
		{
			$app->db->sql('
				DROP TABLE `' . $app->db->prefix . 'files`
				;');
		}

		if ( !empty($app->permission->ready) )
		{
			$app->permission->delete('admin file access');
		}

		break;
	case 'init':
		if ( !empty($app->db->ready) && !empty($app->node->ready) )
		{
			require($controller->classPath . 'File.php');

			$app->file = new file($app);
		}

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Files',
			'description' => 'Upload and manage files',
			'group'       => 'Content',
			'path'        => 'admin/files',
			'permission'  => 'admin file access',
			);

		break;
	case 'route':
		if ( !empty($app->file->ready) )
		{
			if ( $params['parts'][0] == 'file' )
			{
				$params['path'] = 'uploads/index.php';
			}
		}

		break;
	case 'unit_tests':
		/**
		 * Uploading a file
		 */
		$post = array(
			'title[0]'    => 'Unit Test File',
			'file[0]'     => '@' . $controller->rootPath . 'favicon.ico',
			'form-submit' => 'Submit',
			'auth-token'  => $app->input->authToken
			);

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/files/', $post);

		$app->db->sql('
			SELECT
				*
			FROM `' . $app->db->prefix . 'files`
			WHERE
				`title` = "Unit Test File"
			LIMIT 1
			;', FALSE);

		$file = isset($app->db->result[0]) ? $app->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Uploading a file in <code>/admin/files/</code>.',
			'pass' => ( bool ) $file['id']
			);

		/**
		 * Deleting a file
		 */
		if ( $file['id'] )
		{
			$post = array(
				'get_data'   => serialize(array(
					'id'     => ( int ) $file['id'],
					'action' => 'delete'
					)),
				'confirm'    => '1',
				'auth-token' => $app->input->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/files/?id=' . ( int ) $file['id'] . '&action=delete', $post);
		}

		$app->db->sql('
			SELECT
				`id`
			FROM `' . $app->db->prefix . 'files`
			WHERE
				`id` = ' . ( int ) $file['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a file in <code>/admin/files/</code>.',
			'pass' => !$app->db->result
			);

		break;
}

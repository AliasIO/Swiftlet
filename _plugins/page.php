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
			'name'         => 'page',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'node', 'perm'),
			'hooks'        => array('dashboard' => 1, 'init' => 5, 'install' => 1, 'remove' => 1, 'route' => 1, 'unit_tests' => 1)
			);

		break;
	case 'install':
		if ( !in_array($model->db->prefix . 'pages', $model->db->tables) )
		{
			$model->db->sql('
				CREATE TABLE `' . $model->db->prefix . 'pages` (
					`id`        INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
					`node_id`   INT(10)    UNSIGNED NOT NULL,
					`title`     VARCHAR(255)        NOT NULL,
					`body`      TEXT                    NULL,
					`published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
					`lang`      VARCHAR(255)        NOT NULL,
					`date`      DATETIME            NOT NULL,
					`date_edit` DATETIME            NOT NULL,
					INDEX `node_id`   (`node_id`),
					INDEX `published` (`published`),
					PRIMARY KEY (`id`)
					)
				;');
		}

		if ( !empty($model->node->ready) )
		{
			$model->node->create('Pages', 'pages', node::rootId);
		}

		if ( !empty($model->perm->ready) )
		{
			$model->perm->create('Pages', 'admin page access', 'Manage pages');
			$model->perm->create('Pages', 'admin page create', 'Create pages');
			$model->perm->create('Pages', 'admin page edit',   'Edit pages');
			$model->perm->create('Pages', 'admin page delete', 'Delete pages');
		}

		break;
	case 'remove':
		if ( in_array($model->db->prefix . 'pages', $model->db->tables) )
		{
			$model->db->sql('DROP TABLE `' . $model->db->prefix . 'pages`;');
		}

		if ( !empty($model->node->ready) )
		{
			$model->db->sql('
				SELECT
					`id`
				FROM `' . $model->db->prefix . 'nodes`
				WHERE
					`permalink` = "pages"
				LIMIT 1
				;');
			
			if ( $model->db->result && $nodeId = $model->db->result[0]['id'] )
			{
				$model->node->delete($nodeId);
			}
		}

		if ( !empty($model->perm->ready) )
		{
			$model->perm->delete('admin page access');
		}

		break;
	case 'init':
		if ( !empty($model->db->ready) && !empty($model->node->ready) )
		{		
			require($contr->classPath . 'page.php');

			$model->page = new page($model);
		}

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Pages',
			'description' => 'Add and edit pages',
			'group'       => 'Content',
			'path'        => 'admin/pages/',
			'perm'        => 'admin page access',
			'order'       => 1
			);

		break;
	case 'route':
		if ( $model->page->ready )
		{
			if ( $params['parts'][0] == 'p' )
			{
				$params['path'] = 'page.php';
			}
		}

		break;
	case 'unit_tests':
		/**
		 * Creating a page
		 */
		$model->db->sql('
			SELECT
				`id`
			FROM `' . $model->db->prefix . 'nodes`
			WHERE
				`permalink` = "pages"
			LIMIT 1
			;');

		$parentId = isset($model->db->result[0]) ? $model->db->result[0]['id'] : FALSE;

		$post = array(
			'parent'      => $parentId,
			'form-submit' => 'Submit',
			'auth_token'  => $model->authToken
			);

		$languages = !empty($model->lang->ready) ? $model->lang->languages : array('English US');

		foreach ( $languages as $language )
		{
			$post['title[' . $model->h($language) . ']'] = 'Unit Test Page';
			$post['body['  . $model->h($language) . ']'] = 'Unit Test Page - Create';
		}

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'admin/pages/', $post);

		$model->db->sql('
			SELECT
				p.*
			FROM      `' . $model->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $model->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
			WHERE
				n.`title` = "Unit Test Page" AND
				p.`lang`  = "English US"
			LIMIT 1
			;', FALSE);

		$page = isset($model->db->result[0]) ? $model->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Creating a page in <code>/admin/pages/</code>.',
			'pass' => ( bool ) $page['node_id']
			);

		/**
		 * Editing a page
		 */
		if ( $page['node_id'] )
		{
			$post = array(
				'parent'      => $parentId,
				'form-submit' => 'Submit',
				'auth_token'  => $model->authToken
				);

			foreach ( $languages as $language )
			{
				$post['title[' . $model->h($language) . ']'] = 'Unit Test Page';
				$post['body[' . $model->h($language) . ']']  = 'Unit Test Page - Edit';
			}

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'admin/pages/?id=' . ( int ) $page['node_id'] . '&action=edit', $post);
		}

		$model->db->sql('
			SELECT
				`body`
			FROM `' . $model->db->prefix . 'pages`
			WHERE
				`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$body = isset($model->db->result[0]) ? $model->db->result[0]['body'] : FALSE;

		$params[] = array(
			'test' => 'Editing a page in <code>/admin/pages/</code>.',
			'pass' => $body == 'Unit Test Page - Edit'
			);

		/**
		 * Deleting a page
		 */
		if ( $page['node_id'] )
		{
			$post = array(
				'get_data'   => serialize(array(
					'id'     => ( int ) $page['node_id'],
					'action' => 'delete'
					)),
				'confirm'    => '1',
				'auth_token' => $model->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . 'admin/pages/?id=' . ( int ) $page['node_id'] . '&action=delete', $post);
		}

		$model->db->sql('
			SELECT
				n.`id`
			FROM      `' . $model->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $model->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
			WHERE
				p.`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a page in <code>/admin/pages/</code>.',
			'pass' => !$model->db->result
			);
		
		break;
}

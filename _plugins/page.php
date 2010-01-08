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
			'dependencies' => array('db', 'node'),
			'hooks'        => array('admin' => 1, 'header' => 1, 'init' => 5, 'install' => 1, 'unit_tests' => 1, 'url_rewrite' => 1)
			);

		break;
	case 'install':
		$model->db->sql('
			CREATE TABLE `' . $model->db->prefix . 'pages` (
				`id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`node_id`   INT(10) UNSIGNED NOT NULL,
				`title`     VARCHAR(255)     NOT NULL,
				`body`      TEXT             NOT NULL,
				`lang`      VARCHAR(255)     NOT NULL,
				`date`      DATETIME         NOT NULL,
				`date_edit` DATETIME         NOT NULL,
				INDEX (`node_id`),
				PRIMARY KEY (`id`)
				)
			;');

		if ( !empty($model->node->ready) )
		{
			$model->node->create('Pages', 'pages', node::rootId);
		}

		break;
	case 'init':
		if ( !empty($model->db->ready) && !empty($model->node->ready) )
		{		
			require($contr->classPath . 'page.php');

			$model->page = new page($model);
		}

		break;
	case 'admin':
		$params[] = array(
			'name'        => 'Pages',
			'description' => 'Add and edit pages',
			'group'       => 'Content',
			'path'        => 'admin/pages/',
			'auth'        => 3,
			'order'       => 1
			);

		break;
	case 'url_rewrite':
		if ( $model->page->ready && !empty($params['url']) )
		{
			$params['url'] = $model->page->rewrite($params['url']);
		}

		break;
	case 'header':
		if ( !empty($model->page->ready) )
		{
			$model->page->set_page_title();
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
			$post['body[' . $model->h($language) . ']']  = 'Unit Test Page - Create';
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
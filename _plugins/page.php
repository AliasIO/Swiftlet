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
			'name'         => 'page',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('db', 'node', 'permission'),
			'hooks'        => array('dashboard' => 1, 'display_node' => 1, 'home' => 1, 'init' => 5, 'install' => 1, 'remove' => 1, 'unit_tests' => 1)
			);

		break;
	case 'install':
		if ( !in_array($app->db->prefix . 'pages', $app->db->tables) )
		{
			$app->db->sql('
				CREATE TABLE `' . $app->db->prefix . 'pages` (
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
					) TYPE = MyISAM
				;');
		}

		if ( !empty($app->permission->ready) )
		{
			$app->permission->create('Pages', 'admin page access', 'Manage pages');
			$app->permission->create('Pages', 'admin page create', 'Create pages');
			$app->permission->create('Pages', 'admin page edit',   'Edit pages');
			$app->permission->create('Pages', 'admin page delete', 'Delete pages');
		}

		break;
	case 'remove':
		if ( in_array($app->db->prefix . 'pages', $app->db->tables) )
		{
			// Remove nodes
			$app->db->sql('
				SELECT
					`node_id`
				FROM `' . $app->db->prefix . 'pages`
				;');

			if ( $r = $app->db->result )
			{
				foreach ( $r as $d )
				{
					$app->node->delete($d['node_id']);
				}

			}

			$app->db->sql('DROP TABLE `' . $app->db->prefix . 'pages`;');
		}

		if ( !empty($app->permission->ready) )
		{
			$app->permission->delete('admin page access');
		}

		break;
	case 'init':
		if ( !empty($app->db->ready) && !empty($app->node->ready) )
		{
			require($controller->classPath . 'page.php');

			$app->page = new page($app);
		}

		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Pages',
			'description' => 'Add and edit pages',
			'group'       => 'Content',
			'path'        => 'admin/pages/',
			'permission'        => 'admin page access'
			);

		break;
	case 'display_node':
		if ( !empty($app->page->ready) )
		{
			if ( $params['type'] == 'page' )
			{
				$params['path'] = 'page.php';
			}
		}

		break;
	case 'home':
		if ( !empty($app->page->ready) )
		{
			$params['route'] = $app->page->get_home();
		}

		break;
	case 'unit_tests':
		/**
		 * Creating a page
		 */
		$post = array(
			'parent'      => Node::ROOT_ID,
			'path'        => '',
			'form-submit' => 'Submit',
			'auth-token'  => $app->authToken
			);

		$languages = !empty($app->lang->ready) ? $app->lang->languages : array('English US');

		foreach ( $languages as $language )
		{
			$post['title[' . $app->h($language) . ']'] = 'Unit Test Page';
			$post['body['  . $app->h($language) . ']'] = 'Unit Test Page - Create';
		}

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/pages/', $post);

		$app->db->sql('
			SELECT
				p.*
			FROM      `' . $app->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $app->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
			WHERE
				n.`type`  = "page"           AND
				p.`title` = "Unit Test Page" AND
				p.`lang`  = "English US"
			LIMIT 1
			;', FALSE);

		$page = isset($app->db->result[0]) ? $app->db->result[0] : FALSE;

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
				'parent'      => Node::ROOT_ID,
				'path'        => '',
				'form-submit' => 'Submit',
				'auth-token'  => $app->authToken
				);

			foreach ( $languages as $language )
			{
				$post['title[' . $app->h($language) . ']'] = 'Unit Test Page';
				$post['body['  . $app->h($language) . ']'] = 'Unit Test Page - Edit';
			}

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/pages/?id=' . ( int ) $page['node_id'] . '&action=edit', $post);
		}

		$app->db->sql('
			SELECT
				`body`
			FROM `' . $app->db->prefix . 'pages`
			WHERE
				`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$body = isset($app->db->result[0]) ? $app->db->result[0]['body'] : FALSE;

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
				'auth-token' => $app->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/pages/?id=' . ( int ) $page['node_id'] . '&action=delete', $post);
		}

		$app->db->sql('
			SELECT
				n.`id`
			FROM      `' . $app->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $app->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
			WHERE
				p.`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a page in <code>/admin/pages/</code>.',
			'pass' => !$app->db->result
			);

		break;
}

<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Page_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'node', 'permission'),
		$hooks        = array('dashboard' => 1, 'display_node' => 1, 'home' => 1, 'init' => 5, 'init_after' => 1, 'install' => 1, 'remove' => 1, 'unit_tests' => 1)
		;

	/*
	 * Implement install hook
	 */
	function install()
	{
		if ( !in_array($this->app->db->prefix . 'pages', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'pages` (
					`id`          INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
					`node_id`     INT(10)      UNSIGNED NOT NULL,
					`revision_id` INT(10)      UNSIGNED NOT NULL,
					`published`   TINYINT(1)   UNSIGNED NOT NULL DEFAULT 0,
					`lang`        VARCHAR(255)          NOT NULL,
					`date`        DATETIME              NOT NULL,
					`date_edit`   DATETIME              NOT NULL,
					INDEX  `node_id`   (`node_id`),
					INDEX  `published` (`published`),
					UNIQUE `node_lang` (`node_id`, `lang`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

		if ( !in_array($this->app->db->prefix . 'pages_revisions', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'pages_revisions` (
					`id`        INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
					`page_id`   INT(10)    UNSIGNED NOT NULL,
					`title`     VARCHAR(255)        NOT NULL,
					`body`      TEXT                    NULL,
					`date`      DATETIME            NOT NULL,
					INDEX `page_id` (`page_id`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

		if ( isset($this->app->permission) )
		{
			$this->app->permission->create('Pages', 'admin page access', 'Manage pages');
			$this->app->permission->create('Pages', 'admin page create', 'Create pages');
			$this->app->permission->create('Pages', 'admin page edit',   'Edit pages');
			$this->app->permission->create('Pages', 'admin page delete', 'Delete pages');
		}
	}

	/*
	 * Implement remove hook
	 */
	function remove()
	{
		if ( in_array($this->app->db->prefix . 'pages', $this->app->db->tables) )
		{
			// Remove nodes
			$this->app->db->sql('
				SELECT
					`node_id`
				FROM `' . $this->app->db->prefix . 'pages`
				;');

			if ( $r = $this->app->db->result )
			{
				foreach ( $r as $d )
				{
					$this->app->node->delete($d['node_id']);
				}

			}

			$this->app->db->sql('DROP TABLE `' . $this->app->db->prefix . 'pages`;');
		}

		if ( isset($this->app->permission) )
		{
			$this->app->permission->delete('admin page access');
			$this->app->permission->delete('admin page create');
			$this->app->permission->delete('admin page edit');
			$this->app->permission->delete('admin page delete');
		}
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		if ( count($this->app->input->args) >= 2 && $this->app->input->args[0] == 'node' )
		{
			$this->view->controller = 'Page';
		}
	}

	/*
	 * Implement init_after hook
	 * Find out if a custom home page exists
	 */
	function init_after()
	{
		if ( !$this->view->request )
		{
			$this->app->db->sql('
				SELECT
					n.`id`
				FROM      `' . $this->app->db->prefix . 'pages` AS p
				LEFT JOIN `' . $this->app->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
				WHERE
					p.`published` = 1      AND
					n.`type`      = "page" AND
					n.`home`      = 1
				LIMIT 1
				;');

			if ( $r = $this->app->db->result )
			{
				$this->view->controller = 'Page';
				$this->view->args       = array($r[0]['id']);
			}
		}
	}

	/*
	 * Implement dashboard hook
	 * @param array $params
	 */
	function dashboard(&$params)
	{
		$params[] = array(
			'name'        => 'Pages',
			'description' => 'Add and edit pages',
			'group'       => 'Content',
			'path'        => 'admin/page',
			'permission'  => 'admin page access'
			);
	}

	/*
	 * Implement display_node hook
	 * @params array $params
	 */
	function display_node(&$params)
	{
		if ( $params['type'] == 'page' )
		{
			$params['controller'] = 'Page';
		}
	}

	/**
	 * Prefix relative URLs with path to root
	 * @param string $v
	 */
	function parse_urls(&$v)
	{
		preg_match_all('/(<[^<]*(src|href)=["\'])([^\/][^"\']+)/', $v, $m);

		if ( $m )
		{
			for ( $i = 0; $i < count($m[0]); $i ++ )
			{
				if ( !preg_match('/^[a-z]+:\/\//i', $m[3][$i]) )
				{
					$v = str_replace($m[0][$i], $m[1][$i] . $this->view->rootPath . $m[3][$i], $v);
				}
			}
		}
	}

	/*
	 * Implement unit_tests hook
	 * @param array $params
	 */
	function unit_tests(&$params)
	{
		/**
		 * Creating a page
		 */
		$post = array(
			'parent'      => Node_Plugin::ROOT_ID,
			'path'        => '',
			'form-submit' => 'Submit',
			'auth-token'  => $this->app->input->authToken
			);

		$languages = isset($this->app->lang) ? $this->app->lang->languages : array('English US');

		foreach ( $languages as $language )
		{
			$post['title[' . $this->view->h($language) . ']'] = 'Unit Test Page';
			$post['body['  . $this->view->h($language) . ']'] = 'Unit Test Page - Create';
		}

		$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->rootPath . 'admin/page', $post);

		$this->app->db->sql('
			SELECT
				p.*
			FROM      `' . $this->app->db->prefix . 'nodes`           AS  n
			LEFT JOIN `' . $this->app->db->prefix . 'pages`           AS  p ON n.`id`          =  p.`node_id`
			LEFT JOIN `' . $this->app->db->prefix . 'pages_revisions` AS pr ON p.`revision_id` = pr.`id`
			WHERE
				 n.`type`  = "page"           AND
				 p.`lang`  = "English US"     AND
				pr.`title` = "Unit Test Page"
			LIMIT 1
			;', FALSE);

		$page = isset($this->app->db->result[0]) ? $this->app->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Creating a page in <code>/admin/page</code>.',
			'pass' => ( bool ) $page['node_id']
			);

		/**
		 * Editing a page
		 */
		if ( $page['node_id'] )
		{
			$post = array(
				'parent'      => Node_Plugin::ROOT_ID,
				'path'        => '',
				'form-submit' => 'Submit',
				'auth-token'  => $this->app->input->authToken
				);

			foreach ( $languages as $language )
			{
				$post['title[' . $this->view->h($language) . ']'] = 'Unit Test Page';
				$post['body['  . $this->view->h($language) . ']'] = 'Unit Test Page - Edit';
			}

			$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->rootPath . 'admin/page/edit/' . ( int ) $page['node_id'], $post);
		}

		$this->app->db->sql('
			SELECT
				pr.`body`
			FROM      `' . $this->app->db->prefix . 'pages`           AS  p
			LEFT JOIN `' . $this->app->db->prefix . 'pages_revisions` AS pr ON p.`revision_id` = pr.`id`
			WHERE
				p.`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$body = isset($this->app->db->result[0]) ? $this->app->db->result[0]['body'] : FALSE;

		$params[] = array(
			'test' => 'Editing a page in <code>/admin/page</code>.',
			'pass' => $body == 'Unit Test Page - Edit'
			);

		/**
		 * Deleting a page
		 */
		if ( $page['node_id'] )
		{
			$post = array(
				'confirm'    => '1',
				'auth-token' => $this->app->input->authToken
				);

			$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->rootPath . 'admin/page/delete/' . ( int ) $page['node_id'], $post);
		}

		$this->app->db->sql('
			SELECT
				n.`id`
			FROM      `' . $this->app->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $this->app->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
			WHERE
				p.`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a page in <code>/admin/page</code>.',
			'pass' => !$this->app->db->result
			);
	}
}

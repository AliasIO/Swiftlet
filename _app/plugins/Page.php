<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Page_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'node', 'permission'),
		$hooks        = array('dashboard' => 1, 'display_node' => 1, 'home' => 1, 'init' => 5, 'install' => 1, 'remove' => 1, 'unit_tests' => 1)
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

		if ( !empty($this->app->permission->ready) )
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

		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->delete('admin page access');
		}
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		if ( !empty($this->app->db->ready) )
		{
			/**
			 * Check if the pages table exists
			 */
			if ( in_array($this->app->db->prefix . 'pages', $this->app->db->tables) )
			{
				$this->ready = TRUE;

				if ( count($this->app->input->args) >= 2 && $this->app->input->args[0] == 'node' )
				{
					$this->app->view->controller = 'Page';
				}
			}
		}
	}

	/*
	 * Implement dashboard hook
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
	 * @params $params
	 */
	function display_node(&$params)
	{
		if ( $params['type'] == 'page' )
		{
			$params['controller'] = 'Page';
		}
	}

	/*
	 * Implement home hook
	 * @param array $params
	 */
	function home(&$params)
	{
		if ( !empty($this->app->page->ready) )
		{
			$params['route'] = $this->app->page->get_home();
		}
	}

	/**
	 * Rewrite URLs
	 * @param string $url
	 * @return string
	 */
	function rewrite($url)
	{
		$url = preg_replace('/page\/\?page=(.+)?/', 'p/$1', $url);

		return $url;
	}

	/**
	 * Get route to home page
	 * @return string
	 */
	function get_home()
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
			return $this->rewrite('node/' . $r[0]['id']);
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
			'parent'      => Node::ROOT_ID,
			'path'        => '',
			'form-submit' => 'Submit',
			'auth-token'  => $this->app->input->authToken
			);

		$languages = !empty($this->app->lang->ready) ? $this->app->lang->languages : array('English US');

		foreach ( $languages as $language )
		{
			$post['title[' . $view->h($language) . ']'] = 'Unit Test Page';
			$post['body['  . $view->h($language) . ']'] = 'Unit Test Page - Create';
		}

		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/pages/', $post);

		$this->app->db->sql('
			SELECT
				p.*
			FROM      `' . $this->app->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $this->app->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
			WHERE
				n.`type`  = "page"           AND
				p.`title` = "Unit Test Page" AND
				p.`lang`  = "English US"
			LIMIT 1
			;', FALSE);

		$page = isset($this->app->db->result[0]) ? $this->app->db->result[0] : FALSE;

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
				'auth-token'  => $this->app->input->authToken
				);

			foreach ( $languages as $language )
			{
				$post['title[' . $view->h($language) . ']'] = 'Unit Test Page';
				$post['body['  . $view->h($language) . ']'] = 'Unit Test Page - Edit';
			}

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/pages/?id=' . ( int ) $page['node_id'] . '&action=edit', $post);
		}

		$this->app->db->sql('
			SELECT
				`body`
			FROM `' . $this->app->db->prefix . 'pages`
			WHERE
				`id` = ' . ( int ) $page['id'] . '
			LIMIT 1
			;', FALSE);

		$body = isset($this->app->db->result[0]) ? $this->app->db->result[0]['body'] : FALSE;

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
				'auth-token' => $this->app->input->authToken
				);

			$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/pages/?id=' . ( int ) $page['node_id'] . '&action=delete', $post);
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
			'test' => 'Deleting a page in <code>/admin/pages/</code>.',
			'pass' => !$this->app->db->result
			);
	}
}

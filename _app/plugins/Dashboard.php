<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Dashboard_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'permission', 'session'),
		$hooks        = array('init' => 5, 'install' => 1, 'menu' => 2, 'remove' => 1, 'unit_tests' => 1)
		;

	public
		$pages = array()
		;

	/*
	 * Implement install hook
	 */
	function install()
	{
		$this->app->permission->create('Administration', 'admin dashboard access',          'Access to the dashboard');
		$this->app->permission->create('Administration', 'admin dashboard overview access', 'See installation and configuration details');
	}

	/*
	 * Implement remove hook
	 */
	function remove()
	{
		$this->app->permission->delete('dashboard access');
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		// Group pages
		$pages = array();

		$this->app->hook('dashboard', $pages);

		foreach ( $pages as $page )
		{
			$page['path'] = $this->view->route($page['path']);

			if ( !isset($page['permission']) || $this->app->permission->check($page['permission']) )
			{
				if ( !isset($this->pages[$page['group']]) )
				{
					$this->pages[$page['group']] = array();
				}

				$this->pages[$page['group']][] = $page;
			}
		}

		$this->ready = TRUE;
	}

	/*
	 * Implement menu hook
	 * @params array $params
	 */
	function menu(&$params)
	{
		if ( $this->app->permission->check('dashboard access') )
		{
			$params['Dashboard'] = 'admin/dashboard';
		}
	}

	/*
	 * Implement unit tests hook
	 * @params array $params
	 */
	function unit_tests(&$params)
	{
		$post = array();

		$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->view->rootPath . 'admin/dashboard', $post, TRUE);

		$params[] = array(
			'test' => '<code>/admin/dashboard</code> should be inaccessible for guests.',
			'pass' => $r['info']['redirect_count']
			);
	}
}

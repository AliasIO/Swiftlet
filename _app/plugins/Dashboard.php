<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Dashboard_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'permission', 'session'),
		$hooks        = array('init' => 5, 'install' => 1, 'menu' => 2, 'remove' => 1, 'unit_tests' => 1),

		$pages = array()
		;

	function install()
	{
		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->create('Administration', 'dashboard access', 'Access to the dashboard');
		}
	}

	function remove()
	{
		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->delete('dashboard access');
		}
	}

	function init()
	{
		$pages = array();

		$this->app->hook('dashboard', $pages);

		foreach ( $pages as $page )
		{
			$page['path'] = $this->app->view->route($page['path']);

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

	function menu(&$params)
	{
		if ( !empty($this->app->permission->ready) )
		{
			if ( $this->app->permission->check('dashboard access') )
			{
				$params['Dashboard'] = 'admin/dashboard';
			}
		}
	}

	function unit_tests(&$params)
	{
		$r = post_request('http://' . $_SERVER['SERVER_NAME'] . $controller->absPath . 'admin/index.php', array(), TRUE);

		$params[] = array(
			'test' => '<code>/admin/</code> should be inaccessible for guests.',
			'pass' => $r['info']['http_code'] == '302'
			);
	}
}

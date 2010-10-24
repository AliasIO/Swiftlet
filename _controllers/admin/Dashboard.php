<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

/**
 * Dashboard
 * @abstract
 */
 class Dashboard_Controller extends Controller
{
	public
		$pageTitle    = 'Dashboard',
		$dependencies = array('dashboard', 'permission'),
		$inAdmin      = TRUE
		;

	function init()
	{
		if ( !$this->app->permission->check('dashboard access') )
		{
			header('Location: ' . $this->view->route('login?ref=' . $this->request));

			$this->app->end();
		}

		if ( !empty($this->app->input->GET_raw['action']) && $this->app->input->GET_raw['action'] == 'clear_cache' )
		{
			$this->app->clear_cache();

			header('Location: ' . $this->view->route($this->path . '?notice=cache_cleared'));

			$this->app->end();
		}

		$newPlugins = 0;

		if ( isset($this->app->db) )
		{
			foreach ( $this->app->plugins as $plugin )
			{
				$version = $this->app->$plugin->get_version();

				if ( !$version )
				{
					if ( isset($this->app->$plugin->info['hooks']['install']) )
					{
						$newPlugins ++;
					}
				}
			}
		}

		if ( !empty($this->app->input->GET_raw['notice']) )
		{
			switch($this->app->input->GET_raw['notice'])
			{
				case 'cache_cleared':
					$this->view->notice = $this->view->t('The cache has been cleared.');

					break;
			}
		}

		$this->view->newPlugins = $newPlugins;
		$this->view->pages      = $this->app->dashboard->pages;

		$this->view->load('admin/dashboard.html.php');
	}
}

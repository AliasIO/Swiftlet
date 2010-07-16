<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Plugin
 * @abstract
 */
class Plugin
{
	public
		$info = array()
		;

	private
		$app,
		$view,
		$controller
		;

	/**
	 * Initialize
	 * @param object $app
	 * @param string $plugin
	 */
	function __construct($app, $file)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		$hook = 'info';

		require($this->controller->pluginPath . $file);

		if ( empty($info) )
		{
			$app->error(FALSE, 'No plugin info provided in ' . $this->controller->pluginPath . $file . '.', __FILE__, __LINE__);
		}

		$info = array_merge(array(
			'name'         => '',
			'file'         => $file,
			'description'  => '',
			'version'      => '',
			'compatible'   => array('from' => '', 'to' => ''),
			'dependencies' => array(),
			'hooks'        => array()
			), $info);

		if ( !$info['name'] )
		{
			$app->error(FALSE, 'No plugin name provided in ' . $this->controller->pluginPath . $file . '.', __FILE__, __LINE__);
		}

		if ( isset($app->pluginsLoaded[$info['name']]) )
		{
			$app->error(FALSE, 'Plugin name `' . $info['name'] . '` (' . $this->controller->pluginPath . $file . ') already taken by ' . $this->controller->pluginPath . $app->pluginsLoaded[$info['name']]->info['file'] . '.', __FILE__, __LINE__);
		}

		if ( !$info['version'] )
		{
			$app->error(FALSE, 'No version number provided for plugin `' . $info['name'] . '` (' . $this->controller->pluginPath . $file . ').', __FILE__, __LINE__);
		}

		/**
		 * Check if the plugin is compatible with this version of Swiftlet
		 */
		if ( !$info['compatible']['from'] || !$info['compatible']['to'] )
		{
			$app->error(FALSE, 'No compatibility information provided for plugin `' . $info['name'] . '` in ' . $this->controller->pluginPath . $file . '', __FILE__, __LINE__);
		}

		if ( version_compare(Application::VERSION, str_replace('*', '99999', $info['compatible']['from']), '<') || version_compare(Application::VERSION, str_replace('*', '99999', $info['compatible']['to']), '>') )
		{
			$app->error(FALSE, 'Plugin `' . $info['name'] . '` (/' . $this->controller->pluginPath . $file . ') is designed for ' . ( $info['compatible']['from'] == $info['compatible']['to'] ? 'version ' . $info['compatible']['from'] : 'versions ' . $info['compatible']['from'] . ' to ' . $info['compatible']['to'] ) . ' of Swiftlet (running version ' . Model::VERSION . ')', __FILE__, __LINE__);
		}

		if ( $info['hooks'] )
		{
			$app->hook_register($info['name'], $info['hooks']);
		}

		$this->info = $info;
	}

	/**
	 * Hook the plugin
	 * @param string hook
	 */
	function hook($hook, $order, &$params = array())
	{
		$app        = $this->app;
		$view       = $this->app->view;
		$controller = $this->app->controller;

		$timerStart = $app->timer_start();

		require($controller->pluginPath . $this->info['file']);

		$app->pluginsHooked[$this->info['name']][$hook] = TRUE;

		$app->debugOutput['plugins hooked']['hook: ' . $hook][] = array(
			'order'          => $order,
			'plugin'         => $this->info['name'] . ' (' . $controller->pluginPath . $this->info['file'] . ')',
			'execution time' => $app->timer_end($timerStart)
			);
	}

	/**
	 * Install a plugin
	 */
	function install()
	{
		$app        = $this->app;
		$view       = $this->view;
		$controller = $this->controller;

		$hook = 'install';

		require($controller->pluginPath . $this->info['file']);
	}

	/**
	 * Upgrade a plugin
	 */
	function upgrade()
	{
		$app        = $this->app;
		$view       = $this->view;
		$controller = $this->controller;

		$hook = 'upgrade';

		require($controller->pluginPath . $this->info['file']);
	}

	/**
	 * Remove (uninstall) a plugin
	 */
	function remove()
	{
		$app        = $this->app;
		$view       = $this->view;
		$controller = $this->controller;

		$hook = 'remove';

		require($controller->pluginPath . $this->info['file']);
	}

	/**
	 * Get version number of an installed plugin
	 */
	function get_version()
	{
		if ( !empty($this->app->db->ready) && in_array($this->app->db->prefix . 'versions', $this->app->db->tables) )
		{
			$this->app->db->sql('
				SELECT
					`version`
				FROM `' . $this->app->db->prefix . 'versions`
				WHERE
					`plugin` = "' . $this->info['name'] . '"
				LIMIT 1
				;');

			if ( isset($this->app->db->result[0]) && $r = $this->app->db->result[0] )
			{
				return $r['version'];
			}
		}
	}
}

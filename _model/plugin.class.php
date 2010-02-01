<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Plugin
 * @abstract
 */
class plugin
{
	private
		$model,
		$view,
		$contr
		;
	
	public
		$info = array()
		;

	/**
	 * Initialize
	 * @param object $model
	 * @param string $plugin
	 */
	function __construct($model, $file)
	{
		$this->model = $model;
		$this->view  = $model->view;
		$this->contr = $model->contr;

		$view  = $model->view;
		$contr = $model->contr;

		$hook = 'info';

		require($contr->pluginPath . $file);

		if ( empty($info) )
		{
			$model->error(FALSE, 'No plug-in info provided in ' . $contr->pluginPath . $file . '.', __FILE__, __LINE__);
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
			$model->error(FALSE, 'No plug-in name provided in ' . $contr->pluginPath . $file . '.', __FILE__, __LINE__);
		}

		if ( isset($model->pluginsLoaded[$info['name']]) )
		{
			$model->error(FALSE, 'Plug-in name `' . $info['name'] . '` (' . $contr->pluginPath . $file . ') already taken by ' . $contr->pluginPath . $model->pluginsLoaded[$info['name']]->info['file'] . '.', __FILE__, __LINE__);
		}
		
		if ( !$info['version'] )
		{
			$model->error(FALSE, 'No version number provided for plug-in `' . $info['name'] . '` (' . $contr->pluginPath . $file . ').', __FILE__, __LINE__);
		}

		/**
		 * Check if the plug-in is compatible with this version of Swiftlet
		 */
		if ( !$info['compatible']['from'] || !$info['compatible']['to'] )
		{
			$model->error(FALSE, 'No compatibility information provided for plug-in `' . $info['name'] . '` in ' . $contr->pluginPath . $file . '', __FILE__, __LINE__);
		}

		if ( version_compare(model::version, str_replace('*', '99999', $info['compatible']['from']), '<') || version_compare(model::version, str_replace('*', '99999', $info['compatible']['to']), '>') )
		{
			$model->error(FALSE, 'Plug-in `' . $info['name'] . '` (/' . $contr->pluginPath . $file . ') is designed for ' . ( $info['compatible']['from'] == $info['compatible']['to'] ? 'version ' . $info['compatible']['from'] : 'versions ' . $info['compatible']['from'] . ' to ' . $info['compatible']['to'] ) . ' of Swiftlet (running version ' . model::version . ')', __FILE__, __LINE__);
		}

		if ( $info['hooks'] )
		{
			$model->hook_register($info['name'], $info['hooks']);
		}

		$this->info = $info;
	}

	/**
	 * Hook a plugin into the code
	 * @param string hook
	 */
	function hook($hook, $order, &$params = array())
	{
		$model = $this->model;
		$view  = $this->view;
		$contr = $this->contr;

		$timerStart = $model->timer_start();
		
		require($contr->pluginPath . $this->info['file']);

		$model->debugOutput['plugins hooked']['hook: ' . $hook][] =	array(
			'order'          => $order,
			'plugin'         => $this->info['name'] . ' (' . $contr->pluginPath . $this->info['file'] . ')',
			'execution time' => $model->timer_end($timerStart)
			);
	}

	/**
	 * Install a plug-in
	 */
	function install()
	{
		$model = $this->model;
		$view  = $this->view;
		$contr = $this->contr;

		$hook = 'install';

		require($contr->pluginPath . $this->info['file']);		
	}

	/**
	 * Upgrade a plug-in
	 */
	function upgrade()
	{
		$model = $this->model;
		$view  = $this->view;
		$contr = $this->contr;

		$hook = 'upgrade';

		require($contr->pluginPath . $this->info['file']);		
	}

	/**
	 * Remove (uninstall) a plug-in
	 */
	function remove()
	{
		$model = $this->model;
		$view  = $this->view;
		$contr = $this->contr;

		$hook = 'remove';

		require($contr->pluginPath . $this->info['file']);		
	}

	/**
	 * Get version number of an installed plug-in
	 */
	function get_version()
	{
		$model = $this->model;

		if ( !empty($model->db->ready) && in_array($model->db->prefix . 'versions', $model->db->tables) )
		{
			$model->db->sql('
				SELECT
					`version`
				FROM `' . $model->db->prefix . 'versions`
				WHERE
					`plugin` = "' . $this->info['name'] . '"
				LIMIT 1
				;');

			if ( isset($model->db->result[0]) && $r = $model->db->result[0] )
			{
				return $r['version'];
			}
		}
	}
}

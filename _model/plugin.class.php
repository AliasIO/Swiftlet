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
		$contr,

		$plugin
		;

	/**
	 * Initialize plugin
	 * @param object $model
	 * @param string $plugin
	 */
	function __construct($model, $plugin)
	{
		$this->model = $model;
		$this->view  = $model->view;
		$this->contr = $model->contr;

		$view  = $model->view;
		$contr = $model->contr;

		$hook = 'load';

		$pluginVersion = '';
		$compatible    = array('from' => '', 'to' => '');
		$dependencies  = array();

		require($contr->pluginPath . $plugin);

		/**
		 * Check if the plug-in is compatible with this version of Swiftlet
		 */
		if ( !$pluginVersion )
		{
			$model->error(FALSE, 'No version number provided for plug-in "' . $plugin . '"', __FILE__, __LINE__);
		}

		if ( !$compatible['from'] || !$compatible['to'] )
		{
			$model->error(FALSE, 'No compatibility information provided for plug-in "' . $plugin . '"', __FILE__, __LINE__);
		}

		if ( version_compare(model::version, str_replace('*', '99999', $compatible['from']), '<') || version_compare(model::version, str_replace('*', '99999', $compatible['to']), '>') )
		{
			$model->error(FALSE, 'Plug-in "' . $plugin . '" is designed for ' . ( $compatible['from'] == $compatible['to'] ? 'version ' . $compatible['from'] : 'versions ' . $compatible['from'] . ' to ' . $compatible['to'] ) . ' of Swiftlet (running version ' . model::version . ')', __FILE__, __LINE__);
		}

		$this->plugin             = $plugin;
		$this->pluginVersion      = $pluginVersion;
		$this->pluginDependencies = $dependencies;
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

		require($contr->pluginPath . $this->plugin);

		$model->debugOutput['plugins hooked']['hook: ' . $hook][] =	array(
			'order'          => $order,
			'plugin'         => $this->plugin,
			'execution time' => $model->timer_end($timerStart)
			);
	}
	
	/**
	 * Check if a plug-in needs installation
	 */
	function check_install()
	{
		$model = $this->model;
		$view  = $this->view;
		$contr = $this->contr;

		$hook = 'install';

		$sql          = '';
		$description  = '';

		if ( !empty($model->db->ready) )
		{
			require($contr->pluginPath . $this->plugin);
		}

		$installedVersion = $this->get_version();

		return array('sql' => $sql, 'description' => $description, 'dependencies' => $this->pluginDependencies, 'installable' => empty($installedVersion), 'version' => $this->pluginVersion);
	}

	/**
	 * Check if a plug-in needs upgrading
	 */
	function check_upgrade()
	{
		$model = $this->model;
		$view  = $this->view;
		$contr = $this->contr;

		$hook = 'upgrade';

		$sql        = '';
		$upgradable = array('from' => '', 'to' => '');

		$isOutdated       = FALSE;
		$isUpgradable     = FALSE;
		$installedVersion = FALSE;

		if ( !empty($model->db->ready) )
		{
			$installedVersion = $this->get_version();

			if ( $installedVersion && version_compare($installedVersion, $this->pluginVersion, '<') )
			{
				$isOutdated = TRUE;
			}

			if ( $isOutdated )
			{
				require($contr->pluginPath . $this->plugin);

				if ( version_compare($installedVersion, str_replace('*', '99999', $upgradable['from']), '>=') && version_compare($installedVersion, str_replace('*', '99999', $upgradable['to']), '<=') )
				{
					$isUpgradable = TRUE;
				}
			}
		}

		return array('sql' => $sql, 'outdated' => $isOutdated, 'upgradable' => $isUpgradable, 'version' => $this->pluginVersion, 'installed_version' => $installedVersion);
	}
	
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
					`plugin` = "' . $this->plugin . '"
				LIMIT 1
				;');

			if ( isset($model->db->result[0]) && $r = $model->db->result[0] )
			{
				return $r['version'];
			}
		}
	}
}
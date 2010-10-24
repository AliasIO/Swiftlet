<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

/**
 * Home
 * @abstract
 */
class Home_Controller extends Controller
{
	public
		$pageTitle = 'Installation successful'
		;

	function init()
	{
		$newPlugins = 0;

		if ( isset($this->app->db) )
		{
			foreach ( $this->app->plugins as $plugin )
			{
				$version = $this->app->{$plugin}->get_version();

				if ( !$version )
				{
					if ( isset($this->app->{$plugin}->hooks['install']) )
					{
						$newPlugins ++;
					}
				}
			}
		}

		$this->view->notices = array();

		if ( $this->app->configMissing )
		{
			$this->view->notices[] = $this->view->t(
				'No configuration file found. Please copy %1$s to %2$s.',
				array(
					'<code>/_config.default.php</code>',
					'<code>/_config.php</code>'
					)
				);
		}
		else
		{
			if ( $this->app->config['testing'] )
			{
				$this->view->notices[] = $this->view->t(
					'%1$s is set to %2$s in %3$s. Be sure to change it to %4$s when running in a production environment.',
					array(
						'<code>testing</code>',
						'<code>TRUE</code>',
						'<code>/_config.php</code>',
						'<code>FALSE</code>'
						)
					);
			}

			if ( !$this->app->config['sysPassword'] )
			{
				$this->view->notices[] = $this->view->t(
					'%1$s has no value in %2$s. Please change it to a unique password (required for some operations).',
					array(
						'<code>sysPassword</code>',
						'<code>/_config.php</code>'
						)
					);
			}

			if ( empty($this->app->db->ready) )
			{
				$this->view->notices[] = $this->view->t(
					'No database connected (required for some plugins). You may need to change the database settings in %s.',
					'<code>/_config.php</code>'
					);
			}

			if ( $newPlugins )
			{
				$this->view->notices[] = $this->view->t(
					'%1$s Plugin(s) require installation (go to %2$s).',
					array(
						$newPlugins,
						'<a href="' . $this->view->rootPath . 'installer"><code>/installer</code></a>'
						)
					);
			}
		}

		$this->view->load('home.html.php');
	}
}

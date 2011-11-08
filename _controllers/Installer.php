<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Plugin installer
 * @abstract
 */
class Installer_Controller extends Controller
{
	public
		$pageTitle    = 'Plugin installer',
		$dependencies = array('buffer', 'input')
		;

	function init()
	{
		$this->app->input->validate(array(
			'plugin'          => 'bool',
			'system-password' => '/^' . preg_quote($this->app->config['sysPassword'], '/') . '$/',
			'mode'            => 'string',
			'form-submit'     => 'bool',
			));

		if ( !session_id() )
		{
			session_start();
		}

		$authenticated = isset($_SESSION['swiftlet authenticated']);

		$this->newPlugins       = array();
		$this->outdatedPlugins  = array();
		$this->installedPlugins = array();

		$requiredBy = array();

		foreach ( $this->app->plugins as $plugin )
		{
			if ( $this->app->{$plugin}->installed )
			{
				// Removable
				if ( isset($this->app->{$plugin}->hooks['remove']) )
				{
					$this->installedPlugins[$plugin] = $this->app->{$plugin};
				}

				// Upgradable
				if ( isset($this->app->{$plugin}->hooks['upgrade']) && version_compare($this->app->{$plugin}->version, $this->app->{$plugin}->installed, '>') )
				{
					$this->outdatedPlugins[$plugin] = $this->app->{$plugin};

					if ( version_compare($this->app->{$plugin}->installed, str_replace('*', '99999', $this->app->{$plugin}->upgradable['from']), '>=') && version_compare($this->app->{$plugin}->installed, str_replace('*', '99999', $this->app->{$plugin}->upgradable['to']), '<=') )
					{
						$this->outdatedPlugins[$plugin]->upgradable = TRUE;
					}
					else
					{
						$this->outdatedPlugins[$plugin]->upgradable = FALSE;
					}
				}
			}
			else
			{
				// Installable
				$this->newPlugins[$plugin] = $this->app->{$plugin};
			}

			$this->app->{$plugin}->dependencyStatus = array(
				'required by'  => array(),
				'installable'  => array(),
				'installed'    => array(),
				'missing'      => array()
				);
		}

		foreach ( $this->app->plugins as $plugin )
		{
			$this->check_dependencies($plugin, $this->app->{$plugin}->dependencyStatus);

			foreach ( $this->app->{$plugin}->dependencies as $dependency )
			{
				if ( $this->app->{$plugin}->installed )
				{
					$this->app->{$dependency}->dependencyStatus['required by'][] = $plugin;
				}
			}
		}

		ksort($this->newPlugins);

		if ( !$this->app->config['sysPassword'] )
		{
			$this->view->error = $this->view->t('%1$s has no value in %2$s (required).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
		}
		elseif ( !isset($this->app->db) )
		{
			$this->view->error = $this->view->t('No database connected (required). You may need to change the database settings in %1$s.', '<code>/_config.php</code>');
		}
		else
		{
			if ( $this->app->input->POST_valid['form-submit'] )
			{
				/*
				 * Delay the script to prevent brute-force attacks
				 */
				sleep(1);

				if ( $this->app->input->errors )
				{
					$this->view->error = $this->view->t('Incorrect system password.');
				}
				else
				{
					if ( $this->app->input->POST_raw['mode'] == 'authenticate' )
					{
						$_SESSION['swiftlet authenticated'] = TRUE;

						$authenticated = TRUE;
					}
					else if ( $authenticated && $this->app->input->POST_valid['plugin'] && is_array($this->app->input->POST_valid['plugin']) )
					{
						switch ( $this->app->input->POST_raw['mode'] )
						{
							case 'install':
								/**
								 * Create plugin versions table
								 */
								if ( !in_array($this->app->db->prefix . 'versions', $this->app->db->tables) )
								{
									$this->app->db->sql('
										CREATE TABLE `{versions}` (
											`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
											`plugin`  VARCHAR(255)     NOT NULL,
											`version` VARCHAR(10)      NOT NULL,
											PRIMARY KEY (`id`),
											UNIQUE `plugin` (`plugin`)
											) ENGINE = INNODB
										');
								}

								$pluginsInstalled = array();

								foreach ( $this->app->input->POST_valid['plugin'] as $plugin => $v )
								{
									$this->install($plugin, $pluginsInstalled);
								}

								if ( $pluginsInstalled )
								{
									header('Location: ' . $this->view->route($this->request . '?notice=installed&plugins=' . implode('|', $pluginsInstalled), FALSE));

									$this->app->end();
								}

								break;
							case 'upgrade':
								$pluginsUpgraded = array();

								foreach ( $this->app->input->POST_valid['plugin'] as $plugin => $v )
								{
									if ( isset($this->outdatedPlugins[$plugin]) && !$this->app->{$plugin}->dependencyStatus['missing'] )
									{
										$this->app->{$plugin}->upgrade();

										$this->app->db->sql('
											UPDATE {versions} SET
												`version` = :version
											WHERE
												`plugin` = :plugin
											LIMIT 1
											', array(
												':version' => $this->outdatedPlugins[$plugin]->version,
												':plugin'  => $plugin
												)
											);

										$pluginsUpgraded[] = $plugin;

										unset($this->outdatedPlugins[$plugin]);
									}
								}

								if ( $pluginsUpgraded )
								{
									header('Location: ' . $this->view->route($this->request . '?notice=upgraded&plugins=' . implode('|', $pluginsUpgraded), FALSE));

									$this->app->end();
								}

								break;
							case 'remove':
								$pluginsRemoved = array();

								foreach ( $this->app->input->POST_valid['plugin'] as $plugin => $v )
								{
									if ( isset($this->installedPlugins[$plugin]) && !$this->app->{$plugin}->dependencyStatus['required by'] )
									{
										$this->app->db->sql('
											DELETE
											FROM {versions}
											WHERE
												`plugin` = :plugin
											LIMIT 1
											', array(
												':plugin' => $plugin
												)
											);

										$this->app->{$plugin}->remove();

										$pluginsRemoved[] = $plugin;

										unset($this->installedPlugins[$plugin]);
									}
								}

								if ( $pluginsRemoved )
								{
									header('Location: ' . $this->view->route($this->request . '?notice=removed&plugins=' . implode('|', $pluginsRemoved), FALSE));

									$this->app->end();
								}

								break;
						}
					}
				}
			}
		}

		if ( isset($this->app->input->GET_raw['notice']) && isset($this->app->input->GET_raw['plugins']) )
		{
			switch ( $this->app->input->GET_raw['notice'] )
			{
				case 'installed':
					$this->view->notice = $this->view->t('The following plugin(s) have been successfully installed:%1$s', '<br/><br/>' . str_replace('|', '<br/>', $this->app->input->GET_html_safe['plugins']));

					break;
				case 'upgraded':
					$this->view->notice = $this->view->t('The following plugin(s) have been successfully upgraded:%1$s', '<br/><br/>' . str_replace('|', '<br/>', $this->app->input->GET_html_safe['plugins']));

					break;
				case 'removed':
					$this->view->notice = $this->view->t('The following plugin(s) have been successfully removed:%1$s', '<br/><br/>' . str_replace('|', '<br/>', $this->app->input->GET_html_safe['plugins']));

					break;
			}
		}

		$this->view->authenticated    = $authenticated;
		$this->view->newPlugins       = $this->newPlugins;
		$this->view->outdatedPlugins  = $this->outdatedPlugins;
		$this->view->installedPlugins = $this->installedPlugins;

		$this->view->load('installer.html.php');
	}

	/*
	 * Check dependencies recursively
	 * @param string $plugin
	 * @param array $status
	 */
	private function check_dependencies($plugin, &$status)
	{
		if ( !empty($this->app->{$plugin}->dependencies) )
		{
			foreach ( $this->app->{$plugin}->dependencies as $dependency )
			{
				$this->check_dependencies($dependency, $status);

				if ( !isset($this->app->{$dependency}) )
				{
					$status['missing'][] = $dependency;
				}
				else if ( empty($this->app->{$dependency}->installed) )
				{
					$status['installable'][] = $dependency;
				}
				else
				{
					$status['installed'][] = $dependency;
				}
			}
		}

		$status['missing']     = array_unique($status['missing']);
		$status['installable'] = array_unique($status['installable']);
		$status['installed']   = array_unique($status['installed']);
	}

	/*
	 * Recursively install plugins and dependencies
	 * @param string $plugin
	 * @param array $pluginsInstalled
	 */
	private function install($plugin, &$pluginsInstalled)
	{
		if ( isset($this->newPlugins[$plugin]) && !$this->app->{$plugin}->dependencyStatus['missing'] )
		{
			if ( !empty($this->app->{$plugin}->dependencyStatus['installable']) )
			{
				foreach ( $this->app->{$plugin}->dependencyStatus['installable'] as $dependency )
				{
					$this->install($dependency, $pluginsInstalled);
				}
			}

			$this->app->{$plugin}->install();

			$this->app->db->sql('
				INSERT INTO {versions} (
					`plugin`,
					`version`
					)
				VALUES (
					:plugin,
					:version
					)
				', array(
					':plugin'  => $plugin,
					':version' => $this->newPlugins[$plugin]->version
					)
				);

			$pluginsInstalled[] = $plugin;

			unset($this->newPlugins[$plugin]);
		}
	}
}

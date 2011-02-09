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

		$this->view->newPlugins       = array();
		$this->view->outdatedPlugins  = array();
		$this->view->installedPlugins = array();

		if ( isset($this->app->db) )
		{
			$requiredBy = array();

			foreach ( $this->app->plugins as $plugin )
			{
				foreach ( $this->app->{$plugin}->dependencies as $dependency )
				{
					if ( !isset($requiredBy[$dependency]) )
					{
						$requiredBy[$dependency] = array();
					}

					$requiredBy[$dependency][$plugin] = !empty($this->app->{$dependency}->installed) && $this->app->{$plugin}->version ? 1 : 0;
				}
			}

			foreach ( $this->app->plugins as $plugin )
			{
				if ( !$this->app->{$plugin}->installed )
				{
					if ( isset($this->app->{$plugin}->hooks['install']) )
					{
						$dependencyStatus = array();

						foreach ( $this->app->{$plugin}->dependencies as $dependency )
						{
							$dependencyStatus[$dependency] = !empty($this->app->{$dependency}->installed) ? 1 : 0;
						}

						$this->view->newPlugins[$plugin]                    = $this->app->{$plugin};
						$this->view->newPlugins[$plugin]->dependency_status = $dependencyStatus;
					}
				}
				else
				{
					if ( isset($this->app->{$plugin}->hooks['upgrade']) && version_compare($this->app->{$plugin}->version, $version, '>') )
					{
						$dependencyStatus = array();

						foreach ( $this->app->{$plugin}->dependencies as $dependency )
						{
							$dependencyStatus[$dependency] = !empty($this->app->{$dependency}->installed) ? 1 : 0;
						}

						$this->view->outdatedPlugins[$plugin]                    = $this->app->{$plugin};
						$this->view->outdatedPlugins[$plugin]->dependency_status = $dependencyStatus;

						if ( version_compare($version, str_replace('*', '99999', $this->app->{$plugin}->upgradable['from']), '>=') && version_compare($version, str_replace('*', '99999', $this->app->{$plugin}->upgradable['to']), '<=') )
						{
							$this->view->outdatedPlugins[$plugin]->upgradable = TRUE;
						}
						else
						{
							$this->view->outdatedPlugins[$plugin]->upgradable = FALSE;
						}
					}

					if ( isset($this->app->{$plugin}->hooks['remove']) )
					{
						$this->view->installedPlugins[$plugin]                     = $this->app->{$plugin};
						$this->view->installedPlugins[$plugin]->required_by_status = isset($requiredBy[$plugin]) ? $requiredBy[$plugin] : array();
					}
				}
			}
		}

		ksort($this->view->newPlugins);

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
										CREATE TABLE `' . $this->app->db->prefix . 'versions` (
											`id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
											`plugin`  VARCHAR(255)     NOT NULL,
											`version` VARCHAR(10)      NOT NULL,
											PRIMARY KEY (`id`),
											UNIQUE `plugin` (`plugin`)
											) TYPE = INNODB
										;');
								}

								$pluginsInstalled = array();

								foreach ( $this->app->input->POST_valid['plugin'] as $plugin => $v )
								{
									if ( isset($this->view->newPlugins[$plugin]) && !in_array(0, $this->view->newPlugins[$plugin]->dependency_status) )
									{
										$this->app->{$plugin}->install();

										$this->app->db->sql('
											INSERT INTO `' . $this->app->db->prefix . 'versions` (
												`plugin`,
												`version`
												)
											VALUES (
												"' . $this->app->db->escape($plugin)           . '",
												"' . $this->view->newPlugins[$plugin]->version . '"
												)
											;');

										$pluginsInstalled[] = $plugin;

										unset($this->view->newPlugins[$plugin]);
									}
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
									if ( isset($this->view->outdatedPlugins[$plugin]) && !in_array(0, $this->view->outdatedPlugins[$plugin]->dependency_status) )
									{
										$this->app->{$plugin}->upgrade();

										$this->app->db->sql('
											UPDATE `' . $this->app->db->prefix . 'versions` SET
												`version` = "' . $this->view->outdatedPlugins[$plugin]->version . '"
											WHERE
												`plugin` = "' . $this->app->db->escape($plugin) . '"
											LIMIT 1
											;');

										$pluginsUpgraded[] = $plugin;

										unset($this->view->outdatedPlugins[$plugin]);
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
									if ( isset($this->view->installedPlugins[$plugin]) && !in_array(1, $this->view->installedPlugins[$plugin]->required_by_status) )
									{
										$this->app->db->sql('
											DELETE
											FROM `' . $this->app->db->prefix . 'versions`
											WHERE
												`plugin` = "' . $this->app->db->escape($plugin) . '"
											LIMIT 1
											;');

										$this->app->{$plugin}->remove();

										$pluginsRemoved[] = $plugin;

										unset($this->view->installedPlugins[$plugin]);
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

		$this->view->authenticated = $authenticated;

		$this->view->load('installer.html.php');
	}
}

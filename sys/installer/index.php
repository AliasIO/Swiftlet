<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Plug-in installer'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('buffer', 'form'));

$model->form->validate(array(
	'plugin'          => 'bool',
	'system-password' => '/^' . preg_quote($model->sysPassword, '/') . '$/',
	'mode'            => 'string',
	'form-submit'     => 'bool',
	));

$view->newPlugins       = array();
$view->outdatedPlugins  = array();
$view->installedPlugins = array();

if ( isset($model->db) )
{
	$requiredBy = array();

	foreach ( $model->pluginsLoaded as $pluginName => $plugin )
	{
		foreach ( $plugin->info['dependencies'] as $dependency )
		{
			if ( !isset($requiredBy[$dependency]) )
			{
				$requiredBy[$dependency] = array();
			}

			$requiredBy[$dependency][$pluginName] = !empty($model->{$dependency}->ready) && $plugin->get_version() ? 1 : 0;
		}
	}

	foreach ( $model->pluginsLoaded as $pluginName => $plugin )
	{
		$version = $plugin->get_version();

		if ( !$version )
		{
			if ( isset($plugin->info['hooks']['install']) )
			{
				$dependencyStatus = array();

				foreach ( $plugin->info['dependencies'] as $dependency )
				{
					$dependencyStatus[$dependency] = !empty($model->{$dependency}->ready) ? 1 : 0;
				}

				$view->newPlugins[$pluginName]                      = $plugin->info;
				$view->newPlugins[$pluginName]['dependency_status'] = $dependencyStatus;
			}
		}
		else
		{
			if ( isset($plugin->info['hooks']['upgrade']) )
			{
				if ( version_compare($version, str_replace('*', '99999', $plugin->info['upgradable']['from']), '>=') && version_compare($version, str_replace('*', '99999', $plugin->info['upgradable']['to']), '<=') )
				{
					$view->outdatedPlugins[$pluginName] = $plugin->info;
				}
			}
			
			if ( ($plugin->info['hooks']['remove']) )
			{
				$view->installedPlugins[$pluginName]                       = $plugin->info;
				$view->installedPlugins[$pluginName]['required_by_status'] = isset($requiredBy[$pluginName]) ? $requiredBy[$pluginName] : array();
			}
		}
	}
}

ksort($view->newPlugins);

if ( empty($model->db->ready) )
{
	$view->error = $model->t('No database connected (required). You may need to change the database settings in %1$s.', '<code>/_config.php</code>');
}
elseif ( !$model->sysPassword )
{
	$view->error = $model->t('%1$s has no value in %2$s (required).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
}
else
{
	if ( $model->POST_valid['form-submit'] )
	{
		if ( $model->form->errors )
		{
			$view->error = $model->t('Incorrect system password.');
		}
		elseif ( $model->POST_valid['plugin'] && is_array($model->POST_valid['plugin']) )
		{
			if ( $model->POST_raw['mode'] == 'install' ) 
			{
				/**
				 * Create plug-in versions table
				 */			
				if ( !in_array($model->db->prefix . 'versions', $model->db->tables) )
				{
					$model->db->sql('
						CREATE TABLE `' . $model->db->prefix . 'versions` (
							`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
							`plugin`      VARCHAR(256)     NOT NULL,
							`version`     VARCHAR(10)      NOT NULL,
							PRIMARY KEY  (`id`)
							);
						');				
				}

				$pluginsInstalled = array();

				foreach ( $model->POST_valid['plugin'] as $pluginName => $v )
				{
					if ( isset($view->newPlugins[$pluginName]) && !in_array(0, $view->newPlugins[$pluginName]['dependency_status']) )
					{
						$model->pluginsLoaded[$pluginName]->install();

						$model->db->sql('
							INSERT INTO `' . $model->db->prefix . 'versions` (
								`plugin`,
								`version`
								)
							VALUES (
								"' . $model->db->escape($pluginName) . '",
								"' . $view->newPlugins[$pluginName]['version'] . '"
								)
							;');

						$pluginsInstalled[] = $pluginName;

						unset($view->newPlugins[$pluginName]);
					}
				}

				if ( $pluginsInstalled )
				{
					header('Location: ?notice=installed&plugins=' . implode('|', $pluginsInstalled));

					$model->end();
				}
			}
			else if ( $model->POST_raw['mode'] == 'upgrade' )
			{
				$pluginsUpgraded = array();

				foreach ( $model->POST_valid['plugin'] as $pluginName => $v )
				{
					if ( isset($view->outdatedPlugins[$pluginName]) )
					{
						$model->pluginsLoaded[$pluginName]->upgrade();

						$model->db->sql('
							UPDATE `' . $model->db->prefix . 'versions` SET
								`version` = "' . $view->outdatedPlugins[$pluginName]['version'] . '"
							WHERE
								`plugin` = "' . $pluginName . '"
							LIMIT 1
							;');

						$pluginsUpgraded[] = $pluginName;

						unset($view->outdatedPlugins[$pluginName]);
					}
				}

				if ( $pluginsUpgraded )
				{
					header('Location: ?notice=upgraded&plugins=' . implode('|', $pluginsUpgraded));

					$model->end();
				}
			}		
			else if (	$model->POST_raw['mode'] == 'remove' )
			{
				$pluginsRemoved = array();

				foreach ( $model->POST_valid['plugin'] as $pluginName => $v )
				{
					if ( isset($view->installedPlugins[$pluginName]) && !in_array(1, $view->installedPlugins[$pluginName]['required_by_status']) )
					{
						$model->db->sql('
							DELETE
							FROM `' . $model->db->prefix . 'versions`
							WHERE
								`plugin` = "' . $model->db->escape($pluginName) . '"
							LIMIT 1
							;');

						$model->pluginsLoaded[$pluginName]->remove();

						$pluginsRemoved[] = $pluginName;

						unset($view->installedPlugins[$pluginName]);
					}
				}

				if ( $pluginsRemoved )
				{
					header('Location: ?notice=removed&plugins=' . implode('|', $pluginsRemoved));

					$model->end();
				}
			}
		}
	}
}

if ( isset($model->GET_raw['notice']) && isset($model->GET_raw['plugins']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'installed':
			$view->notice = $model->t('The following plug-in(s) have been successfully installed:<br/><br/>%1$s', str_replace('|', '<br/>', $model->GET_html_safe['plugins']));

			break;
		case 'upgraded':
			$view->notice = $model->t('The following plug-in(s) have been successfully upgraded:<br/><br/>%1$s', str_replace('|', '<br/>', $model->GET_html_safe['plugins']));

			break;
		case 'removed':
			$view->notice = $model->t('The following plug-in(s) have been successfully removed:<br/><br/>%1$s', str_replace('|', '<br/>', $model->GET_html_safe['plugins']));

			break;
	}
}

$view->load('installer.html.php');

$model->end();

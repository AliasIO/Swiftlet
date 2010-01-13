<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Plug-in installer'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('buffer', 'form'));

$model->form->validate(array(
	'plugin'          => 'bool',
	'system_password' => '/^' . preg_quote($model->sysPassword, '/') . '$/',
	'mode'            => 'string',
	'form-submit'     => 'bool',
	));

$view->new_plugins      = array();
$view->outdated_plugins = array();

if ( isset($model->db) )
{
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

				$view->new_plugins[$pluginName]                      = $plugin->info;
				$view->new_plugins[$pluginName]['dependency_status'] = $dependencyStatus;
			}
		}
		else
		{
			if ( isset($plugin->info['hook']['upgrade']) )
			{
				if ( version_compare($version, str_replace('*', '99999', $plugin->info['upgradable']['from']), '>=') && version_compare($version, str_replace('*', '99999', $plugin->info['upgradable']['to']), '<=') )
				{
					$view->outdated_plugins[$pluginName] = $plugin->info;
				}
			}
		}
	}
}

ksort($view->new_plugins);

if ( empty($model->db->ready) )
{
	$view->error = $model->t('No database connected (required). You may need to change the database settings in %1$s.', '<code>/_config.php</code>');
}
elseif ( !$model->sysPassword )
{
	$view->error = $model->t('%1$s has no value in %1$s (required).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
}
else
{
	if ( $model->POST_valid['form-submit'] )
	{
		if ( $model->form->errors )
		{
			$view->error =  $model->t('Incorrect system password.');
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

				$plugins_installed = array();

				foreach ( $model->POST_valid['plugin'] as $pluginName => $v )
				{
					if ( isset($view->new_plugins[$pluginName]) && !in_array(0, $view->new_plugins[$pluginName]['dependency_status']) )
					{
						$model->pluginsLoaded[$pluginName]->install();

						$model->db->sql('
							INSERT INTO `' . $model->db->prefix . 'versions` (
								`plugin`,
								`version`
								)
							VALUES (
								"' . $model->db->escape($pluginName) . '",
								"' . $view->new_plugins[$pluginName]['version'] . '"
								);
							');

						$plugins_installed[] = $pluginName;

						unset($view->new_plugins[$pluginName]);
					}
				}

				if ( $plugins_installed )
				{
					header('Location: ?notice=installed&plugins=' . implode('|', $plugins_installed));

					$model->end();
				}
			}
			elseif ( $model->POST_raw['mode'] == 'upgrade' )
			{
				$plugins_upgraded = array();

				foreach ( $model->POST_valid['plugin'] as $pluginName => $v )
				{
					if ( isset($view->outdated_plugins[$pluginName]) )
					{
						$model->pluginsLoaded[$pluginName]->upgrade();

						$model->db->sql('
							UPDATE `' . $model->db->prefix . 'versions` SET
								`version` = "' . $view->outdated_plugins[$pluginName]['version'] . '"
							WHERE
								`plugin` = "' . $pluginName . '"
							LIMIT 1
							;');

						$plugins_upgraded[] = $pluginName;

						unset($view->outdated_plugins[$pluginName]);
					}
				}

				if ( $plugins_upgraded )
				{
					header('Location: ?notice=upgraded&plugins=' . implode('|', $plugins_upgraded));

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
	}
}

if ( $view->new_plugins )
{
	$view->install_notice = 'Please select the plug-ins you wish to install. The system password is stored in <code>/_config.php</code>';
}
elseif ( !empty($model->db->ready) )
{
		$view->install_notice = 'There are no uninstalled plug-ins.';
}

if ( $view->outdated_plugins )
{
	$view->upgrade_notice = 'Please select the plug-ins you wish to upgrade. The system password is stored in <code>/_config.php</code>';
}
elseif ( !empty($model->db->ready) )
{
		$view->upgrade_notice = 'There are no outdated plug-ins.';
}

$view->load('installer.html.php');

$model->end();
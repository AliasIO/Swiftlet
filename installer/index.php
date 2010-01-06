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
	foreach ( $model->pluginsLoaded as $pluginFile => $plugin )
	{
		if ( $v = $plugin->check_install() )
		{
			$v['is_ready'] = array();

			foreach ( $v['dependencies'] as $dependency )
			{
				$v['is_ready'][$dependency] = !empty($model->{$dependency}->ready) ? 1 : 0;
			}

			if ( $v['installable'] && $v['sql'] )
			{
				$view->new_plugins[$pluginFile] = $v;
			}
		}

		if ( $v = $plugin->check_upgrade() )
		{
			if ( $v['outdated'] )
			{
				$view->outdated_plugins[$pluginFile] = $v;
			}
		}
	}
}

ksort($view->new_plugins);

if ( empty($model->db->ready) )
{
	$view->error = 'No database connected (required). You may need to change the database settings in <code>/_config.php</code>.';
}
elseif ( !$model->sysPassword )
{
	$view->error = '<code>sysPassword</code> has no value in <code>/_config.php</code> (required).';
}
else
{
	if ( $model->POST_valid['form-submit'] )
	{
		if ( $model->form->errors )
		{
			$view->error = 'Incorrect system password.';
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

				foreach ( $model->POST_valid['plugin'] as $plugin => $v )
				{
					if ( isset($view->new_plugins[$plugin]) )
					{
						$queries = $view->new_plugins[$plugin]['sql'];

						if ( !is_array($queries) )
						{
							$queries = array($queries);
						}

						foreach ( $queries as $sql )
						{
							if ( trim($sql) )
							{
								$model->db->sql($sql);
							}
						}

						$model->db->sql('
							INSERT INTO `' . $model->db->prefix . 'versions` (
								`plugin`,
								`version`
								)
							VALUES (
								"' . $model->db->escape($plugin) . '",
								"' . $view->new_plugins[$plugin]['version'] . '"
								);
							');

						$plugins_installed[] = $plugin;

						unset($view->new_plugins[$plugin]);
					}
				}

				if ( $plugins_installed )
				{
					header('Location: ?notice=installed&plugins=' . implode('|', $plugins_installed));
				}
			}
			elseif ( $model->POST_raw['mode'] == 'upgrade' )
			{
				$plugins_upgraded = array();

				foreach ( $model->POST_valid['plugin'] as $plugin => $v )
				{
					if ( isset($view->outdated_plugins[$plugin]) )
					{
						foreach ( explode(';', $view->outdated_plugins[$plugin]['sql']) as $sql )
						{
							if ( trim($sql) ) $model->db->sql($sql);
						}

						$model->db->sql('
							UPDATE `' . $model->db->prefix . 'versions` SET
								`version` = "' . $view->outdated_plugins[$plugin]['version'] . '"
							WHERE
								`plugin` = "' . $plugin . '"
							LIMIT 1
							;');

						$plugins_upgraded[] = $plugin;

						unset($view->outdated_plugins[$plugin]);
					}
				}

				if ( $plugins_upgraded )
				{
					header('Location: ?notice=upgraded&plugins=' . implode('|', $plugins_upgraded));
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
			$view->notice = 'The following plug-in(s) have been successfully installed:<br/><br/>' . str_replace('|', '<br/>', $model->GET_html_safe['plugins']);

			break;
		case 'upgraded':
			$view->notice = 'The following plug-in(s) have been successfully upgraded:<br/><br/>' . str_replace('|', '<br/>', $model->GET_html_safe['plugins']);

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
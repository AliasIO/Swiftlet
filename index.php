<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => './',
	'pageTitle' => 'Up and running'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$new_plugins = array();

if ( isset($model->db) )
{
	foreach ( $model->pluginsLoaded as $pluginFile => $plugin )
	{
		if ( $v = $plugin->check_install() )
		{
			if ( $v['installable'] && $v['sql'] )
			{
				$new_plugins[] = $pluginFile;
			}
		}
	}
}

$view->notices = array();

if ( $model->debugMode )                       $view->notices[] = $model->t('%1$s is turned on in %2$s. Be sure to turn it off when running in a production environment.', array('<code>debugMode</code>', '<code>/_config.php</code>'));
if ( is_dir($contr->rootPath . 'unit_tests') ) $view->notices[] = $model->t('Please remove the %1$s directory when running in a production environment.', '<a href="' . $view->rootPath . 'unit_tests/"><code>/unit_tests/</code></a>');
if ( !$model->sysPassword )                    $view->notices[] = $model->t('%1$s has no value in %2$s. Please change it to a unique password (required for some operations).', array('<code>sysPassword</code>', '<code>/_config.php</code>'));
if ( empty($model->db->ready) )                $view->notices[] = $model->t('No database connected (required for some plug-ins). You may need to change the database settings in %s.', '<code>/_config.php</code>');
if ( count($new_plugins) )                     $view->notices[] = $model->t('%1$s Plug-in(s) require installation (go to %2$s).', array(count($new_plugins), '<a href="' . $view->rootPath . 'installer/"><code>/installer/</code></a>'));

$view->load('index.html.php');

$model->end();
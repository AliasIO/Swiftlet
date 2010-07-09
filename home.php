<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => './',
	'pageTitle' => 'Installation successful'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$newPlugins = 0;

if ( isset($model->db) )
{
	foreach ( $model->pluginsLoaded as $pluginName => $plugin )
	{
		$version = $plugin->get_version();
		
		if ( !$version )
		{
			if ( isset($plugin->info['hooks']['install']) )
			{
				$newPlugins ++;
			}
		}
	}
}

$view->notices = array();

if ( $model->configMissing )
{
	$view->notices[] = $model->t(
		'No configuration file found. Please copy %1$s to %2$s.',
		array(
			'<code>/_config.default.php</code>',
			'<code>/_config.php</code>'
			)
		);
}
else
{
	if ( $model->testing )
	{
		$view->notices[] = $model->t(
			'%1$s is set to %2$s in %3$s. Be sure to change it to %4$s when running in a production environment.',
			array(
				'<code>testing</code>',
				'<code>TRUE</code>',
				'<code>/_config.php</code>',
				'<code>FALSE</code>'
				)
			);
	}

	if ( !$model->sysPassword )
	{
		$view->notices[] = $model->t(
			'%1$s has no value in %2$s. Please change it to a unique password (required for some operations).',
			array(
				'<code>sysPassword</code>',
				'<code>/_config.php</code>'
				)
			);
	}

	if ( empty($model->db->ready) )
	{
		$view->notices[] = $model->t(
			'No database connected (required for some plug-ins). You may need to change the database settings in %s.',
			'<code>/_config.php</code>'
			);
	}

	if ( $newPlugins )
	{
		$view->notices[] = $model->t(
			'%1$s Plug-in(s) require installation (go to %2$s).',
			array(
				$newPlugins,
				'<a href="' . $view->rootPath . 'installer/"><code>/installer/</code></a>'
				)
			);
	}
}

$view->load('home.html.php');

$model->end();

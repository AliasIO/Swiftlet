<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Dashboard',
	'inAdmin'   => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('dashboard', 'perm'));

if ( !$model->perm->check('dashboard access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

if ( !empty($model->GET_raw['action']) && $model->GET_raw['action'] == 'clear_cache' )
{
	$model->clear_cache();

	header('Location: ?notice=cache_cleared');

	$model->end();
}

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

if ( !empty($model->GET_raw['notice']) )
{
	switch($model->GET_raw['notice'])
	{
		case 'cache_cleared':
			$view->notice = $model->t('The cache has been cleared.');

			break;
	}
}

$view->newPlugins = $newPlugins;
$view->pages      = $model->dashboard->pages;

$view->load('admin/dashboard.html.php');

$model->end();

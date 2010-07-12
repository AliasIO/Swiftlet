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

require($contrSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('dashboard', 'perm'));

if ( !$app->perm->check('dashboard access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

if ( !empty($app->GET_raw['action']) && $app->GET_raw['action'] == 'clear_cache' )
{
	$app->clear_cache();

	header('Location: ?notice=cache_cleared');

	$app->end();
}

$newPlugins = 0;

if ( isset($app->db) )
{
	foreach ( $app->pluginsLoaded as $pluginName => $plugin )
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

if ( !empty($app->GET_raw['notice']) )
{
	switch($app->GET_raw['notice'])
	{
		case 'cache_cleared':
			$view->notice = $app->t('The cache has been cleared.');

			break;
	}
}

$view->newPlugins = $newPlugins;
$view->pages      = $app->dashboard->pages;

$view->load('admin/dashboard.html.php');

$app->end();

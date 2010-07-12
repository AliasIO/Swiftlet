<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'   => './',
	'pageTitle'  => 'Route',
	'standAlone' => TRUE
	);

require($contrSetup['rootPath'] . 'init.php');

$route = '';

if ( !empty($app->GET_raw['q']) )
{
	$route = $app->GET_raw['q'];
}

if ( !$route )
{
	$params = array(
		'route' => ''
		);

	$app->hook('home', $params);

	$route = $params['route'];
}

if ( !$route )
{
	chdir($contr->rootPath);

	require('home.php');
}

$params = array(
	'parts' => explode('/', $route),
	'path'  => ''
	);

$app->hook('route', $params);

$app->routeParts = $params['parts'];

if ( $path = $params['path'] )
{
	chdir($contr->rootPath . dirname($path));

	require($path);
}

/*
 * Page not found
 */
chdir($contr->rootPath);

require('404.php');

$app->end();

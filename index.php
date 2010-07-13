<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'   => './',
	'pageTitle'  => 'Route',
	'standAlone' => TRUE
	);

require($controllerSetup['rootPath'] . 'init.php');

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
	chdir($controller->rootPath);

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
	chdir($controller->rootPath . dirname($path));

	require($path);
}

/*
 * Page not found
 */
chdir($controller->rootPath);

require('404.php');

$app->end();

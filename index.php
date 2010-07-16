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

$view->route = array(
	'path'       => '',
	'controller' => '',
	'parts'      => array(),
	'action'     => '',
	'id'         => ''
	);

if ( !empty($app->input->GET_raw['q']) )
{
	$view->route['path'] = $app->input->GET_raw['q'];
}

if ( !$view->route['path'] )
{
	$params = array(
		'route' => ''
		);

	$app->hook('home', $params);

	$view->route['path'] = $params['route'];
}

if ( !$view->route['path'] )
{
	$view->route['path'] = 'home';
}

$params = array(
	'parts'      => explode('/', $view->route['path']),
	'controller' => ''
	);

$app->hook('route', $params);

$view->route['parts']      = $params['parts'];
$view->route['controller'] = $params['controller'];

for ( $i = count($view->route['parts']); $i > 0; $i -- )
{
	$file = implode('/', array_slice($view->route['parts'], 0, $i));

	if ( is_file($controller->controllerPath . $file . '.php') )
	{
		$view->route['controller'] = $file;

		$view->route['action'] = isset($view->route['parts'][$i])     ? $view->route['parts'][$i]     : '';
		$view->route['id']     = isset($view->route['parts'][$i + 1]) ? $view->route['parts'][$i + 1] : '';
	}
}

if ( $view->route['controller'] )
{
	chdir($controller->controllerPath . dirname($view->route['controller']));

	require(basename($view->route['controller']) . '.php');

	exit;
}

/*
 * Page not found
 */
chdir($controller->controllerPath);

require('404.php');

$app->end();

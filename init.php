<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Check PHP version
 */
if ( version_compare(PHP_VERSION, '5.1', '<') )
{
	die('<p>PHP 5.1 or higher is required.</p>');
}

if ( empty($controllerSetup) )
{
	die('<p>Missing controller setup.</p>');
}

if ( !class_exists('Application') )
{
	require($controllerSetup['rootPath'] . '_app/Application.php');
}

if ( !class_exists('Controller') )
{
	require($controllerSetup['rootPath'] . '_app/Controller.php');
}

if ( isset($controller) )
{
	$absPath = $controller->absPath;
}

$controller = new Controller($controllerSetup);

if ( isset($absPath) )
{
	$controller->absPath = $absPath;
}

if ( !isset($app) )
{
	$app = new Application($controller);
}
else
{
	$app->controller = $controller;

	$app->view = new View($app, $view->route);
}

if ( !class_exists('View') )
{
	$app->view = new View($app);
}

$view = $app->view;

unset($controllerSetup);

if ( empty($controller->standAlone) )
{
	$app->hook('header');
}

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

if ( empty($contrSetup) )
{
	die('<p>Missing controller setup.</p>');
}

if ( !class_exists('model') )
{
	require($contrSetup['rootPath'] . '_app/model.class.php');
}

if ( !class_exists('controller') )
{
	require($contrSetup['rootPath'] . '_app/controller.class.php');
}

if ( isset($contr) )
{
	$absPath = $contr->absPath;
}

$contr = new controller($contrSetup);

if ( isset($absPath) )
{
	$contr->absPath = $absPath;
}

if ( !isset($app) )
{
	$app = new model($contr);
}
else
{
	$app->contr = $contr;

	$app->view = new view($app);
}

if ( !class_exists('view') )
{
	$app->view = new view($app);
}

$view = $app->view;

unset($contrSetup);

if ( empty($contr->standAlone) )
{
	$app->hook('header');
}

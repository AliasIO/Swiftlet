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
	require($contrSetup['rootPath'] . '_model/model.class.php');
}

if ( !class_exists('contr') )
{
	require($contrSetup['rootPath'] . '_model/controller.class.php');
}

if ( isset($contr) )
{
	$absPath = $contr->absPath;
}

$contr = new contr($contrSetup);

if ( isset($absPath) )
{
	$contr->absPath = $absPath;
}

if ( !isset($model) )
{
	$model = new model($contr);
}
else
{
	$model->contr = $contr;

	$model->view = new view($model);
}

if ( !class_exists('view') )
{
	$model->view = new view($model);
}

$view = $model->view;

unset($contrSetup);

if ( empty($contr->standAlone) )
{
	$model->hook('header');
}

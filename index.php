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

require($contrSetup['rootPath'] . '_model/init.php');

$route = '';

if ( !empty($model->GET_raw['q']) )
{
	$route = $model->GET_raw['q'];
}

if ( !$route )
{
	chdir($contr->rootPath);

	require('home.php');
}

$model->routeParts = explode('/', $route);

$params = array(
	'parts' => $model->routeParts,
	'path'  => ''
	);

$model->hook('route', $params);

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

$model->end();

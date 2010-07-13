<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => './',
	'pageTitle' => 'Page not found'
	);

require($controllerSetup['rootPath'] . 'init.php');

$view->load('404.html.php');

$app->end();

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

$view->pages = $model->dashboard->pages;

$view->load('admin/dashboard.html.php');

$model->end();

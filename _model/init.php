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

require($contrSetup['rootPath'] . '_model/model.class.php');
require($contrSetup['rootPath'] . '_model/controller.class.php');

$contr = new contr($contrSetup);
$model = new model($contr);

$view = $model->view;

unset($contrSetup);

if ( empty($contr->standAlone) )
{
	$model->hook('header');
}

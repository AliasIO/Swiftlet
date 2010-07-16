<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'   => '../',
	'pageTitle'  => 'CKEditor config',
	'standAlone' => TRUE
	);

require($controllerSetup['rootPath'] . 'init.php');

header('Content-type: text/javascript');

$view->load('scripts/ckeditor.js.php');

$app->end();

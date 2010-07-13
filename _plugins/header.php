<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'info':
		$info = array(
			'name'       => 'header',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('init' => 999, 'header' => 999)
			);

		break;
	case 'init':
		require($controller->classPath . 'header.php');

		$app->header = new header($app);

		break;
	case 'header':
		$view->load('header.html.php');

		break;
}

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
			'name'       => 'input',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('footer' => 1, 'init' => 1)
			);

		break;
	case 'init':
		require($controller->classPath . 'Input.php');

		$app->input = new input($app);

		break;
	case 'footer':
		if ( !empty($app->input->errors) )
		{
			$view->load('input_errors.html.php');
		}
}

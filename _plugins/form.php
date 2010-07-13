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
			'name'       => 'form',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('footer' => 1, 'init' => 1)
			);

		break;	
	case 'init':
		require($controller->classPath . 'form.php');

		$app->form = new form($app);

		break;
	case 'footer':
		if ( !empty($app->form->errors) )
		{
			$view->load('form_errors.html.php');
		}
}

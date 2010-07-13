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
			'name'       => 'email',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('init' => 1, 'email' => 1)
			);

		break;
	case 'init':
		require($controller->classPath . 'email.php');

		$app->email = new email($app);

		break;
	case 'email':
		if ( !empty($app->email->ready) )
		{
			$params['success'] = $app->email->send($params);
		}
}

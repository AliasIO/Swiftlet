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
			'name'       => 'buffer',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('init' => 4, 'end' => 999, 'error' => 999)
			);

		break;
	case 'init':
		require($contr->classPath . 'buffer.php');

		$app->buffer = new buffer($app);

		$app->buffer->start();

		break;
	case 'end':
		if ( !empty($app->buffer->ready) )
		{
			$app->debugOutput['buffer output size'] = round(strlen(ob_get_contents()) / 1024 / 1024, 3) . ' MB';

			$app->buffer->flush();
		}

		break;
	case 'error':
		if ( !empty($app->buffer->ready) )
		{
			$app->buffer->clean();
		}

		break;
}

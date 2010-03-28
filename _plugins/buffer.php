<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

switch ( $hook )
{
	case 'info':
		$info = array(
			'name'       => 'buffer',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('init' => 1, 'end' => 999, 'error' => 999, 'footer' => 1)
			);

		break;
	case 'init':
		require($contr->classPath . 'buffer.php');

		$model->buffer = new buffer($model);

		$model->buffer->start();

		break;
	case 'end':
		$model->debugOutput['buffer output size'] = round(strlen(ob_get_contents()) / 1024 / 1024, 3) . ' MB';

		if ( $model->debugMode && !$contr->standAlone )
		{
			echo "\n<!--\n\n[ DEBUG OUTPUT ]\n\n";

			print_r($model->debugOutput);

			echo "\n-->";
		}

		$model->buffer->flush();

		break;
	case 'error':
		if ( !empty($model->buffer->ready) )
		{
			$model->buffer->clean();
		}

		break;
}

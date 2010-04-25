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
			'name'         => 'cache',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('buffer'),			
			'hooks'        => array('cache' => 1, 'init' => 999, 'end' => 999)
			);

		break;
	case 'init':
		require($contr->classPath . 'cache.php');

		$model->cache = new cache($model);

		break;
	case 'cache':
		if ( !empty($model->cache->ready) )
		{
			$model->cache->write($params['contents']);
		}

		break;
}

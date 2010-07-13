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
			'name'         => 'cache',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('buffer'),			
			'hooks'        => array('cache' => 1, 'clear_cache' => 1, 'init' => 999, 'end' => 999)
			);

		break;
	case 'init':
		require($controller->classPath . 'cache.php');

		$app->cache = new cache($app);

		break;
	case 'cache':
		if ( !empty($app->cache->ready) )
		{
			$app->cache->write($params['contents']);
		}

		break;
	case 'clear_cache':
		if ( !empty($app->cache->ready) )
		{
			$app->cache->clear();
		}

		break;
}

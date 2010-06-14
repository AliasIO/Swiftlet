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
			'name'       => 'doc',
			'version'    => '1.0.0',
			'compatible' => array('from' => '1.2.0', 'to' => '1.2.*'),
			'hooks'      => array('dashboard' => 999, 'init' => 1, 'route' => 1)
			);

		break;
	case 'init':
		require($contr->classPath . 'doc.php');

		$model->doc = new doc($model);
		
		break;
	case 'dashboard':
		$params[] = array(
			'name'        => 'Documentation',
			'description' => 'Source code documentation and manual',
			'group'       => 'Developer',
			'path'        => 'docs/',
			);

		break;
	case 'route':
		if ( $model->doc->ready )
		{
			if ( $params['parts'][0] == 'docs' )
			{
				$params['path'] = 'docs/index.php';
			}
		}

		break;
}

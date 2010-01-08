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
			'name'         => 'time',
			'version'      => '1.0.0',
			'compatible'   => array('from' => '1.2.0', 'to' => '1.2.*'),
			'dependencies' => array('user'),
			'hooks'        => array('init' => 5, 'install' => 1, 'format_date' => 1)
			);

		break;
	case 'install':
		if ( !empty($model->user->ready) )
		{
			$timeZones = array(
				'Test'
				);

			$model->user->save_pref(array(
				'pref'   => 'Time zone',
				'type'   => 'select',
				'match'  => '/.*/',
				'values' => serialize($timeZones)
				));
		}
		
		break;
	case 'init':
		if ( !empty($model->db->ready) && !empty($model->node->ready) )
		{
			require($contr->classPath . 'time.php');

			$model->time = new time($model);
		}

		break;
	case 'format_date':
		if ( !empty($model->time->ready) )
		{
			$params['date'] = $model->time->format_date($params['date'], $params['type']);
		}

		break;
}
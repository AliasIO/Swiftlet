<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Menu',
	'inAdmin'   => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'form', 'menu', 'node', 'perm'));

$model->form->validate(array(
	'form-submit' => 'bool',
	'items'       => '/.*/',
	));

if ( !$model->perm->check('admin menu access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

if ( $model->POST_valid['form-submit'] )
{
	if ( $model->form->errors )
	{
		$view->error = $model->t('Please correct the errors below.');
	}
	else
	{
		$items = array();

		foreach ( explode("\n", $model->POST_raw['items']) as $item )
		{
			if ( trim($item) )
			{
				$nodeId = '';
				$title  = '';
				$path   = trim($item);

				if ( strstr($item, '|') )
				{
					list($title, $path) = explode('|', $item);
				}

				if ( preg_match('/^node\/([0-9]+)$/', trim($path), $m) )
				{
					$nodeId = ( int ) $m[1];
					$path   = '';
				}

				if ( $path )
				{
					$model->db->sql('
						SELECT
							`id`
						FROM `' . $model->db->prefix . 'nodes`
						WHERE
							`path` = "' . $model->db->escape($path) . '"
						LIMIT 1
						;');

					if ( $r = $model->db->result )
					{
						$nodeId = $r[0]['id'];
						$path   = '';
					}
				}

				$items[] = array(
					'node_id' => ( int ) $nodeId,
					'title'   => trim($title),
					'path'    => trim($path)
					);
			}
		}

		$model->db->sql('
			UPDATE `' . $model->db->prefix . 'menu` SET
				`items` = "' . $model->db->escape(serialize($items)) . '"
			LIMIT 1
			;');

		if ( $model->db->result )
		{
			header('Location: ?notice=success');

			$model->db->end();
		}
	}
}

else if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'success':
			$view->notice = $model->t('The changes have been saved.');
			
			break;
	}
}

$model->db->sql('
	SELECT
		`items`
	FROM `' . $model->db->prefix . 'menu`
	LIMIT 1
	;');

if ( $r = $model->db->result )
{
	if ( $items = unserialize($r[0]['items']) )
	{
		$nodeIds = array();
		$paths   = array();
		
		foreach ( $items as $item )
		{
			if ( ( int ) $item['node_id'] )
			{
				$nodeIds[] = ( int ) $item['node_id'];
			}
		}

		if ( $nodeIds )
		{
			$model->db->sql('
				SELECT
					`id`,
					`path`
				FROM `' . $model->db->prefix . 'nodes`
				WHERE
					`id` IN ( ' . implode(',', $nodeIds) . ' )
				LIMIT ' . count($nodeIds) . '
				;');

			if ( $r = $model->db->result )
			{
				foreach ( $r as $d )
				{
					$paths[$d['id']] = $d['path'];
				}
			}
		}

		$v = '';

		foreach ( $items as $item )
		{
			if ( ( in_array($item['node_id'], $nodeIds) && isset($paths[$item['node_id']]) ) || !in_array($item['node_id'], $nodeIds) )
			{
				$path = isset($paths[$item['node_id']]) ? $paths[$item['node_id']] : ( $item['path'] ? $item['path'] : 'node/' . $item['node_id'] );
				
				$v .= $item['title'] . ( $item['title'] ? '|' : '' ) . $path . "\n";
			}
		}

		$model->POST_html_safe['items'] = $model->h(trim($v));
	}
}

$view->load('admin/menu.html.php');

$model->end();

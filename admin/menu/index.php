<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Menu',
	'inAdmin'   => TRUE
	);

require($controllerSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'form', 'menu', 'node', 'permission'));

$app->form->validate(array(
	'form-submit' => 'bool',
	'items'       => '/.*/',
	));

if ( !$app->permission->check('admin menu access') )
{
	header('Location: ' . $controller->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

if ( $app->POST_valid['form-submit'] )
{
	if ( $app->form->errors )
	{
		$view->error = $app->t('Please correct the errors below.');
	}
	else
	{
		$items = array();

		foreach ( explode("\n", $app->POST_html_safe['items']) as $item )
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
					$app->db->sql('
						SELECT
							`id`
						FROM `' . $app->db->prefix . 'nodes`
						WHERE
							`path` = "' . $app->db->escape($path) . '"
						LIMIT 1
						;');

					if ( $r = $app->db->result )
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

		$app->db->sql('
			UPDATE `' . $app->db->prefix . 'menu` SET
				`items` = "' . $app->db->escape(serialize($items)) . '"
			LIMIT 1
			;');

		if ( $app->db->result )
		{
			header('Location: ?notice=success');

			$app->end();
		}
	}
}
else if ( isset($app->GET_raw['notice']) )
{
	switch ( $app->GET_raw['notice'] )
	{
		case 'success':
			$view->notice = $app->t('The changes have been saved.');

			break;
	}
}

$app->db->sql('
	SELECT
		`items`
	FROM `' . $app->db->prefix . 'menu`
	LIMIT 1
	;');

if ( $r = $app->db->result )
{
	if ( $items = @unserialize($r[0]['items']) )
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
			$app->db->sql('
				SELECT
					`id`,
					`path`
				FROM `' . $app->db->prefix . 'nodes`
				WHERE
					`id` IN ( ' . implode(',', $nodeIds) . ' )
				LIMIT ' . count($nodeIds) . '
				;');

			if ( $r = $app->db->result )
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
				$path = !empty($paths[$item['node_id']]) ? $paths[$item['node_id']] : ( $item['path'] ? $item['path'] : 'node/' . $item['node_id'] );

				$v .= $item['title'] . ( $item['title'] ? '|' : '' ) . $path . "\n";
			}
		}

		$app->POST_html_safe['items'] = trim($v);
	}
}

$view->load('admin/menu.html.php');

$app->end();

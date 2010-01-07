<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Pages',
	'inAdmin'   => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'form', 'node', 'session', 'user'));

$model->form->validate(array(
	'form-submit' => 'bool',
	'delete'      => 'bool',
	'title'       => 'string',
	'body'        => '/.+/',
	'parent'      => 'int'
	));

$id     = isset($model->GET_raw['id']) && ( int ) $model->GET_raw['id'] ? ( int ) $model->GET_raw['id'] : FALSE;
$action = isset($model->GET_raw['action']) && $id ? $model->GET_raw['action'] : FALSE;

if ( $model->session->get('user auth') < user::admin )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

$languages = !empty($model->lang->ready) ? $model->lang->languages : array('English US');

if ( $model->POST_valid['form-submit'] )
{
	foreach ( $model->POST_valid['title'] as $language => $title )
	{
		if ( !$model->POST_valid['title'][$language] )
		{
			$model->form->errors['title'][$language] = 'Please provide a title';
		}
	}

	foreach ( $model->POST_valid['body'] as $language => $title )
	{
		if ( !$model->POST_valid['body'][$language] )
		{
			$model->form->errors['body'][$language] = 'Please provide any content';
		}
	}

	if ( $model->form->errors )
	{
		$view->error = 'Please correct the errors below.';
	}
	else
	{
		switch ( $action )
		{
			case 'edit':
				// Update page
				$permalink = $model->node->permalink($model->POST_db_safe['title']['English US'], $id);
				
				$view->permalink = $permalink;

				$model->db->sql('
					UPDATE `' . $model->db->prefix . 'nodes` SET
						`title`     = "' . $model->POST_db_safe['title']['English US'] . '",
						`permalink` = "' . $permalink . '"
					WHERE
						`id` = ' . $id . '
					LIMIT 1
					;');

				// Check in which languages the page is stored
				$langExist = array();

				$model->db->sql('
					SELECT
						`lang`
					FROM `' . $model->db->prefix . 'pages`
					WHERE
						`node_id` = ' . $id . '
					;');

				if ( $r = $model->db->result )
				{
					foreach ( $r as $d )
					{
						$langExist[] = $d['lang'];
					}
				}

				foreach ( $languages as $language )
				{
					if ( in_array($language, $langExist) )
					{
						$model->db->sql('
							UPDATE `' . $model->db->prefix . 'pages` SET
								`title`     = "' . $model->POST_db_safe['title'][$language] . '",
								`body`      = "' . $model->POST_db_safe['body'][$language] . '",
								`date_edit` = NOW()
							WHERE
								`node_id` = ' . $id . ' AND
								`lang`    = "' . $model->db->escape($language) . '"
							LIMIT 1
							;');
					}
					else
					{
						$model->db->sql('
							INSERT INTO `' . $model->db->prefix . 'pages` (
								`node_id`,
								`title`,
								`body`,
								`lang`,
								`date`,
								`date_edit`
								)
							VALUES (
								' . $id . ',
								"' . $model->POST_db_safe['title'][$language] . '",
								"' . $model->POST_db_safe['body'][$language] . '",
								"' . $model->db->escape($language) . '",
								NOW(),
								NOW()
								)
							;');
					}
				}

				$model->node->move($id, $model->POST_raw['parent']);

				header('Location: ?action=edit&id=' . $id . '&permalink=' . $permalink . '&notice=updated');

				$model->end();

				break;
			default:
				// Create page
				$permalink = $model->node->permalink($model->POST_db_safe['title']['English US']);

				$node_id = $model->node->create($model->POST_db_safe['title']['English US'], $permalink, $model->POST_raw['parent']);

				if ( $node_id )
				{
					foreach ( $languages as $language )
					{
						$model->db->sql('
							INSERT INTO `' . $model->db->prefix . 'pages` (
								`node_id`,
								`title`,
								`body`,
								`lang`,
								`date`,
								`date_edit`
								)
							VALUES (
								' . $node_id . ',
								"' . $model->POST_db_safe['title'][$language] . '",
								"' . $model->POST_db_safe['body'][$language] . '",
								"' . $model->db->escape($language) . '",
								NOW(),
								NOW()
								)
							;');
					}

					if ( $model->db->result )
					{
						header('Location: ?action=edit&id=' . $node_id . '&permalink=' . $permalink . '&notice=created');

						exit;
					}
				}
		}
	}
}
else if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'created':
			$view->notice = 'The page has been created.';
			
			break;
		case 'updated':
			$view->notice = 'The page has been updated.';
			
			break;
		case 'deleted':
			$view->notice = 'The page has been deleted.';
			
			break;
	}
}

switch ( $action )
{
	case 'edit':
		$node = $model->node->get_parents($id);

		if ( $node )
		{
			$model->db->sql('
				SELECT
					p.`title`,
					p.`body`,
					p.`lang`,
					n.`left_id`,
					n.`right_id`,
					n.`permalink`
				FROM      `' . $model->db->prefix . 'nodes` AS n
				LEFT JOIN `' . $model->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
				WHERE
					n.`id`= ' . ( int ) $id . '
				;');

			if ( $r = $model->db->result )
			{
				$editLeftId  = $r[0]['left_id'];
				$editRightId = $r[0]['right_id'];

				$view->permalink = $r[0]['permalink'];

				foreach ( $r as $d )
				{
					$model->POST_html_safe['title'][$d['lang']] = $d['title'];
					$model->POST_html_safe['body'][$d['lang']]  = $d['body'];
				}

				$model->POST_html_safe['parent'] = $node['parents'][count($node['parents']) - 1]['id'];
			}
		}

		break;
	case 'delete':
		if ( !$model->POST_valid['confirm'] )
		{
			$model->confirm('Are you sure you wish to delete this page?');
		}
		else
		{
			// Delete page
			if ( $model->node->delete($id) )
			{
				$model->db->sql('
					DELETE
					FROM `' . $model->db->prefix . 'pages`
					WHERE
						`node_id` = ' . ( int ) $id . '
					;');

				if ( $model->db->result )
				{
					header('Location: ?notice=deleted');

					$model->end();
				}
			}
		}

		break;
}

foreach ( $languages as $language )
{
	if ( !isset($model->POST_html_safe['title'][$language]) )
	{
		$model->POST_html_safe['title'][$language] = '';
	}

	if ( !isset($model->POST_html_safe['body'][$language]) )
	{
		$model->POST_html_safe['body'][$language] = '';
	}
}

$list        = array();
$listParents = array();

$model->db->sql('
	SELECT
		`id`,
		`left_id`,
		`right_id`,
		`title`
	FROM      `' . $model->db->prefix . 'nodes`
	WHERE
		`permalink` = "pages"
	LIMIT 1
	;');

if ( $r = $model->db->result )
{
	$nodePages = $r[0];

	$list = array(
		0 => array(
			'id'        => $nodePages['id'],
			'left_id'   => $nodePages['left_id'],
			'right_id'  => $nodePages['right_id'],
			'title'     => $nodePages['title'],
			'permalink' => 'pages',
			'level'     => 0
			)
		);

	$nodes = $model->node->get_children($nodePages['id']);

	$model->node->nodes_to_array($nodes['children'], $list);

	$listParents = $list;

	// Remove the main pages node from the editable pages dropdown
	array_shift($list);
	
	foreach ( $list as $i => $item )
	{
		$list[$i]['level'] = $list[$i]['level'] - 1;
	}
	
	// A page can not be a child of itself or a descendant, remove those pages from dropdown
	if ( $action == 'edit' )
	{
		foreach ( $listParents as $i => $d )
		{
			if ( $d['left_id'] >= $editLeftId && $d['right_id'] <= $editRightId )
			{
				unset($listParents[$i]);
			}			
		}
	}
}

$view->nodesParents = $listParents;
$view->nodes        = $list;

if ( !isset($view->permalink) )
{
	$view->permalink = '';
}

$view->id        = $id;
$view->action    = $action;
$view->languages = $languages;

$view->load('admin/pages.html.php');

$model->end();
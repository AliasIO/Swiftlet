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

$model->check_dependencies(array('db', 'form', 'node', 'perm'));

$model->form->validate(array(
	'form-submit' => 'bool',
	'delete'      => 'bool',
	'title'       => 'string',
	'body'        => '/.*/',
	'published'   => 'bool',
	'parent'      => 'int'
	));

$id     = isset($model->GET_raw['id']) && ( int ) $model->GET_raw['id'] ? ( int ) $model->GET_raw['id'] : FALSE;
$action = isset($model->GET_raw['action']) && $id ? $model->GET_raw['action'] : FALSE;

if ( !$model->perm->check('admin page access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

$languages = !empty($model->lang->ready) ? $model->lang->languages : array('English US');

sort($languages);

if ( $model->POST_valid['form-submit'] )
{
	foreach ( $model->POST_valid['title'] as $language => $title )
	{
		if ( !$model->POST_valid['title'][$language] )
		{
			$model->form->errors['title_' . array_search($language, $languages)] = $model->t('Please provide a title');
		}
	}

	if ( $model->form->errors )
	{
		$view->error = $model->t('Please correct the errors below.');
	}
	else
	{
		switch ( $action )
		{
			case 'edit':
				if ( $model->perm->check('admin page edit') )
				{
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
									`published` = ' . ( $model->POST_raw['published'] ? 1 : 0 ) . ',
									`date_edit` = "' . gmdate('Y-m-d H:i:s') . '"
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
									`published`,
									`lang`,
									`date`,
									`date_edit`
									)
								VALUES (
									' . $id . ',
									"' . $model->POST_db_safe['title'][$language] . '",
									"' . $model->POST_db_safe['body'][$language] . '",
									' . ( $model->POST_raw['published'] ? 1 : 0 ) . ',
									"' . $model->db->escape($language) . '",
									"' . gmdate('Y-m-d H:i:s') . '",
									"' . gmdate('Y-m-d H:i:s') . '"
									)
								;');
						}
					}

					$model->node->move($id, $model->POST_raw['parent']);

					header('Location: ?action=edit&id=' . $id . '&permalink=' . $permalink . '&notice=updated');

					$model->end();
				}

				break;
			default:
				if ( $model->perm->check('admin page create') )
				{
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
									`published`,
									`lang`,
									`date`,
									`date_edit`
									)
								VALUES (
									' . $node_id . ',
									"' . $model->POST_db_safe['title'][$language] . '",
									"' . $model->POST_db_safe['body'][$language] . '",
									' . ( $model->POST_raw['published'] ? 1 : 0 ) . ',
									"' . $model->db->escape($language) . '",
									"' . gmdate('Y-m-d H:i:s') . '",
									"' . gmdate('Y-m-d H:i:s') . '"
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
}
else if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'created':
			$view->notice = $model->t('The page has been created.');
			
			break;
		case 'updated':
			$view->notice = $model->t('The page has been updated.');
			
			break;
		case 'deleted':
			$view->notice = $model->t('The page has been deleted.');
			
			break;
	}
}

switch ( $action )
{
	case 'edit':
		if ( $model->perm->check('admin page edit') )
		{
			$node = $model->node->get_parents($id);

			if ( $node )
			{
				$model->db->sql('
					SELECT
						p.`title`,
						p.`body`,
						p.`published`,
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

					$model->POST_html_safe['parent']    = $node['parents'][count($node['parents']) - 1]['id'];
					$model->POST_html_safe['published'] = $r[0]['published'] ? 1 : 0;
				}
			}
		}

		break;
	case 'delete':
		if ( $model->perm->check('admin page delete') )
		{
			if ( !$model->POST_valid['confirm'] )
			{
				$model->confirm($model->t('Are you sure you wish to delete this page?'));
			}
			else
			{
				// Delete page
				if ( $model->node->delete($id) )
				{
					// Not using LIMIT 1 because a node can have several pages (translations)
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
		}

		break;
	default:
		$model->POST_html_safe['published'] = 1;
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
		*
	FROM `' . $model->db->prefix . 'nodes` AS n
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
			'date'      => $nodePages['date'],
			'permalink' => 'pages',
			'level'     => 0
			)
		);

	$nodes = $model->node->get_children($nodePages['id']);

	if ( !empty($nodes['children']) )
	{
		$model->node->nodes_to_array($nodes['children'], $list);
	}

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

$pagination = $model->paginate('pages', count($list), 10);

$view->nodesParents    = $listParents;
$view->nodes           = array_splice($list, $pagination['from'], 10);
$view->nodesPagination = $pagination;

if ( !isset($view->permalink) )
{
	$view->permalink = '';
}

$view->id        = $id;
$view->action    = $action;
$view->languages = $languages;

$view->load('admin/pages.html.php');

$model->end();

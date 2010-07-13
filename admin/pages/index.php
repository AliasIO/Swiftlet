<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$controllerSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Pages',
	'inAdmin'   => TRUE
	);

require($controllerSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'form', 'node', 'permission'));

$app->form->validate(array(
	'form-submit' => 'bool',
	'delete'      => 'bool',
	'title'       => 'string',
	'body'        => '/.*/',
	'path'        => '/^(([a-z0-9_\-]+\/)*[a-z0-9_\-]+)*$/i',
	'published'   => 'bool',
	'parent'      => 'int',
	'home'        => 'bool'
	));

$id     = isset($app->GET_raw['id']) && ( int ) $app->GET_raw['id'] ? ( int ) $app->GET_raw['id'] : FALSE;
$action = isset($app->GET_raw['action']) && $id ? $app->GET_raw['action'] : FALSE;

if ( !$app->permission->check('admin page access') )
{
	header('Location: ' . $controller->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$app->end();
}

$languages = !empty($app->lang->ready) ? $app->lang->languages : array('English US');

sort($languages);

if ( $app->POST_valid['form-submit'] )
{
	foreach ( $app->POST_valid['title'] as $language => $title )
	{
		if ( !$app->POST_valid['title'][$language] )
		{
			$app->form->errors['title_' . array_search($language, $languages)] = $app->t('Please provide a title');
		}
	}

	if ( isset($app->form->errors['path']) )
	{
		$app->form->errors['path'] = $app->t('Invalid path. Please use alphanumeric characters, underscores, dashes and slashes only (e.g. "some/path").');
	}

	if ( $app->form->errors )
	{
		$view->error = $app->t('Please correct the errors below.');
	}
	else
	{
		if ( $app->POST_valid['home'] )
		{
			$app->db->sql('
				UPDATE `' . $app->db->prefix . 'nodes` SET
					`home` = 0
				;');
		}

		switch ( $action )
		{
			case 'edit':
				if ( $app->permission->check('admin page edit') )
				{
					$app->db->sql('
						UPDATE `' . $app->db->prefix . 'nodes` SET
							`title` = "' . $app->POST_db_safe['title']['English US'] . '",
							`home`  =  ' . ( $app->POST_raw['home'] ? 1 : 0 )        . ',
							`path`  = "' . $app->POST_db_safe['path']                . '"
						WHERE
							`id` = ' . $id . '
						LIMIT 1
						;');

					// Check in which languages the page is stored
					$langExist = array();

					$app->db->sql('
						SELECT
							`lang`
						FROM `' . $app->db->prefix . 'pages`
						WHERE
							`node_id` = ' . $id . '
						;');

					if ( $r = $app->db->result )
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
							$app->db->sql('
								UPDATE `' . $app->db->prefix . 'pages` SET
									`title`     = "' . $app->POST_db_safe['title'][$language]  . '",
									`body`      = "' . $app->POST_db_safe['body'][$language]   . '",
									`published` =  ' . ( $app->POST_raw['published'] ? 1 : 0 ) . ',
									`date_edit` = "' . gmdate('Y-m-d H:i:s')                     . '"
								WHERE
									`node_id` =  ' . $id . ' AND
									`lang`    = "' . $app->db->escape($language) . '"
								LIMIT 1
								;');
						}
						else
						{
							$app->db->sql('
								INSERT INTO `' . $app->db->prefix . 'pages` (
									`node_id`,
									`title`,
									`body`,
									`published`,
									`lang`,
									`date`,
									`date_edit`
									)
								VALUES (
									 ' . $id                                       . ',
									"' . $app->POST_db_safe['title'][$language]  . '",
									"' . $app->POST_db_safe['body'][$language]   . '",
									 ' . ( $app->POST_raw['published'] ? 1 : 0 ) . ',
									"' . $app->db->escape($language)             . '",
									"' . gmdate('Y-m-d H:i:s')                     . '",
									"' . gmdate('Y-m-d H:i:s')                     . '"
									)
								;');
						}
					}

					$app->node->move($id, $app->POST_raw['parent']);

					$path = !empty($app->POST_raw['path']) ? $app->POST_raw['path'] : 'node/' . ( int ) $id;

					header('Location: ?action=edit&id=' . ( int ) $id . '&path=' . rawurlencode($path) . '&notice=updated');

					$app->end();
				}

				break;
			default:
				if ( $app->permission->check('admin page create') )
				{
					$nodeId = $app->node->create($app->POST_raw['title']['English US'], 'page', $app->POST_raw['parent']);

					if ( $nodeId )
					{
						$app->db->sql('
							UPDATE `' . $app->db->prefix . 'nodes` SET
								`home` =  ' . ( $app->POST_raw['home'] ? 1 : 0 ) . ',
								`path` = "' . $app->POST_db_safe['path']         . '"
							WHERE
								`id` = ' . ( $nodeId ) . '
							LIMIT 1
							;');

						$app->db->result = FALSE;
						
						foreach ( $languages as $language )
						{
							$app->db->sql('
								INSERT INTO `' . $app->db->prefix . 'pages` (
									`node_id`,
									`title`,
									`body`,
									`published`,
									`lang`,
									`date`,
									`date_edit`
									)
								VALUES (
									 ' . ( int ) $nodeId                           . ',
									"' . $app->POST_db_safe['title'][$language]  . '",
									"' . $app->POST_db_safe['body'][$language]   . '",
									 ' . ( $app->POST_raw['published'] ? 1 : 0 ) . ',
									"' . $app->db->escape($language)             . '",
									"' . gmdate('Y-m-d H:i:s')                     . '",
									"' . gmdate('Y-m-d H:i:s')                     . '"
									)
								;');
						}

						if ( $app->db->result )
						{
							$path = !empty($app->POST_raw['path']) ? $app->POST_raw['path'] : 'node/' . $nodeId;

							header('Location: ?action=edit&id=' . $nodeId . '&path=' . rawurlencode($path) . '&notice=created');

							exit;
						}
						else
						{
							$app->node->delete($nodeId);
						}
					}
			}
		}
	}
}
else if ( isset($app->GET_raw['notice']) )
{
	switch ( $app->GET_raw['notice'] )
	{
		case 'created':
			$view->notice = $app->t('The page has been created (%1$sview%2$s).', array('<a href="' . $app->route($app->h($app->GET_raw['path'])) . '">', '</a>'));
			
			break;
		case 'updated':
			$view->notice = $app->t('The page has been updated (%1$sview%2$s).', array('<a href="' . $app->route($app->h($app->GET_raw['path'])) . '">', '</a>'));
			
			break;
		case 'deleted':
			$view->notice = $app->t('The page has been deleted.');
			
			break;
	}
}

switch ( $action )
{
	case 'edit':
		$editLeftId  = 0;
		$editRightId = 0;

		if ( $app->permission->check('admin page edit') )
		{
			$node = $app->node->get_parents($id);

			if ( $node )
			{
				$app->db->sql('
					SELECT
						p.`title`,
						p.`body`,
						p.`published`,
						p.`lang`,
						n.`left_id`,
						n.`right_id`,
						n.`home`,
						n.`path`
					FROM      `' . $app->db->prefix . 'nodes` AS n
					LEFT JOIN `' . $app->db->prefix . 'pages` AS p ON n.`id` = p.`node_id`
					WHERE
						n.`id`= ' . ( int ) $id . '
					;');

				if ( $r = $app->db->result )
				{
					$editLeftId  = ( int ) $r[0]['left_id'];
					$editRightId = ( int ) $r[0]['right_id'];

					foreach ( $r as $d )
					{
						$app->POST_html_safe['title'][$d['lang']] = $d['title'];
						$app->POST_html_safe['body'][$d['lang']]  = $d['body'];
					}

					$app->POST_html_safe['parent']    = ( int ) $node['parents'][count($node['parents']) - 1]['id'];
					$app->POST_html_safe['published'] = $r[0]['published'] ? 1 : 0;
					$app->POST_html_safe['home']      = $r[0]['home']      ? 1 : 0;
					$app->POST_html_safe['path']      = $r[0]['path'];
				}
			}
		}

		break;
	case 'delete':
		if ( $app->permission->check('admin page delete') )
		{
			if ( !$app->POST_valid['confirm'] )
			{
				$app->confirm($app->t('Are you sure you wish to delete this page?'));
			}
			else
			{
				// Delete page
				if ( $app->node->delete($id) )
				{
					// Not using LIMIT 1 because a node can have several pages (translations)
					$app->db->sql('
						DELETE
						FROM `' . $app->db->prefix . 'pages`
						WHERE
							`node_id` = ' . ( int ) $id . '
						;');

					if ( $app->db->result )
					{
						header('Location: ?notice=deleted');

						$app->end();
					}
				}
			}
		}

		break;
	default:
		$app->POST_html_safe['published'] = 1;
}

foreach ( $languages as $language )
{
	if ( !isset($app->POST_html_safe['title'][$language]) )
	{
		$app->POST_html_safe['title'][$language] = '';
	}

	if ( !isset($app->POST_html_safe['body'][$language]) )
	{
		$app->POST_html_safe['body'][$language] = '';
	}
}

$list        = array();
$listParents = array();

$nodes = $app->node->get_children(Node::ROOT_ID, 'page');

$app->node->nodes_to_array($nodes, $list);

array_shift($list);

foreach ( $list as $i => $item )
{
	$list[$i]['level'] --;
}

$listParents = $list;

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

$pagination = $view->paginate('pages', count($list), 10);

$view->nodesParents    = $listParents;
$view->nodes           = array_splice($list, $pagination['from'], 10);
$view->nodesPagination = $pagination;
$view->path            = !empty($view->path) ? $view->path : 'node/' . $id;

$view->id        = $id;
$view->action    = $action;
$view->languages = $languages;

$view->load('admin/pages.html.php');

$app->end();

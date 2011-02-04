<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Page
 * @abstract
 */
class Page_Controller extends Controller
{
	public
		$pageTitle    = 'Pages',
		$dependencies = array('db', 'input', 'node', 'permission'),
		$inAdmin      = TRUE
		;

	function init()
	{
		$this->app->input->validate(array(
			'form-submit' => 'bool',
			'delete'      => 'bool',
			'title'       => 'string',
			'body'        => '/.*/',
			'path'        => '/^(([a-z0-9_\-]+\/)*[a-z0-9_\-]+)*$/i',
			'published'   => 'bool',
			'parent'      => 'int',
			'home'        => 'bool',
			'revision'    => 'empty, int'
			));

		if ( !$this->app->permission->check('admin page access') )
		{
			header('Location: ' . $this->view->route('login?ref=' . $this->request, FALSE));

			$this->app->end();
		}

		$languages = !empty($this->app->lang->ready) ? $this->app->lang->languages : array('English US');

		sort($languages);

		if ( $this->app->input->POST_valid['form-submit'] )
		{
			foreach ( $this->app->input->POST_valid['title'] as $language => $title )
			{
				if ( !$this->app->input->POST_valid['title'][$language] )
				{
					$this->app->input->errors['title_' . array_search($language, $languages)] = $this->view->t('Please provide a title');
				}
			}

			if ( isset($this->app->input->errors['path']) )
			{
				$this->app->input->errors['path'] = $this->view->t('Invalid path. Please use alphanumeric characters, underscores, dashes and slashes only (e.g. "some/path").');
			}

			if ( $this->app->input->errors )
			{
				$this->view->error = $this->view->t('Please correct the errors below.');
			}
			else
			{
				if ( $this->app->input->POST_valid['home'] )
				{
					$this->app->db->sql('
						UPDATE `' . $this->app->db->prefix . 'nodes` SET
							`home` = 0
						;');
				}

				switch ( $this->action )
				{
					case 'edit':
						if ( $this->app->permission->check('admin page edit') )
						{
							$this->app->db->sql('
								UPDATE `' . $this->app->db->prefix . 'nodes` SET
									`title` = "' . $this->app->input->POST_db_safe['title']['English US'] . '",
									`home`  =  ' . ( $this->app->input->POST_raw['home'] ? 1 : 0 )        . ',
									`path`  = "' . $this->app->input->POST_db_safe['path']                . '"
								WHERE
									`id` = ' . $this->id . '
								LIMIT 1
								;');

							// Check in which languages the page is stored
							$langExist = array();

							$this->app->db->sql('
								SELECT
									`id`,
									`lang`
								FROM `' . $this->app->db->prefix . 'pages`
								WHERE
									`node_id` = ' . $this->id . '
								;');

							if ( $r = $this->app->db->result )
							{
								foreach ( $r as $d )
								{
									$langExist[$d['lang']] = $d['id'];
								}
							}

							foreach ( $languages as $language )
							{
								$pageId = FALSE;

								if ( isset($langExist[$language]) )
								{
									$pageId = $langExist[$language];

									$this->app->db->sql('
										UPDATE `' . $this->app->db->prefix . 'pages` SET
											`published` =  ' . ( $this->app->input->POST_raw['published'] ? 1 : 0 ) . ',
											`date_edit` = "' . gmdate('y-m-d h:i:s')                                . '"
										WHERE
											`id` =  ' . $pageId . '
										LIMIT 1
										;');
								}
								else
								{
									$this->app->db->sql('
										INSERT INTO `' . $this->app->db->prefix . 'pages` (
											`node_id`,
											`published`,
											`lang`,
											`date`,
											`date_edit`
											)
										VALUES (
											 ' . $this->id                                            . ',
											 ' . ( $this->app->input->POST_raw['published'] ? 1 : 0 ) . ',
											"' . $this->app->db->escape($language)                    . '",
											"' . gmdate('Y-m-d H:i:s')                                . '",
											"' . gmdate('Y-m-d H:i:s')                                . '"
											)
										;');

									$pageId = $this->app->db->result;
								}

								if ( $pageId )
								{
									$this->app->db->sql('
										INSERT INTO `' . $this->app->db->prefix . 'pages_revisions` (
											`page_id`,
											`title`,
											`body`,
											`date`
											)
										VALUES (
											 ' . $pageId                                             . ',
											"' . $this->app->input->POST_db_safe['title'][$language] . '",
											"' . $this->app->input->POST_db_safe['body'][$language]  . '",
											"' . gmdate('Y-m-d H:i:s')                               . '"
											)
										;');

									$revisionId = $this->app->db->result;

									if ( $this->app->input->POST_db_safe['revision'] )
									{
										$revisionId = ( int ) $this->app->input->POST_db_safe['revision'];
									}

									if ( $revisionId )
									{
										$this->app->db->sql('
											UPDATE `' . $this->app->db->prefix . 'pages` SET
												`revision_id` =  ' . ( int ) $revisionId . '
											WHERE
												`id` =  ' . $pageId . '
											LIMIT 1
											;');
									}
								}
							}

							$this->app->node->move($this->id, $this->app->input->POST_raw['parent']);

							$path = !empty($this->app->input->POST_raw['path']) ? $this->app->input->POST_raw['path'] : 'page/' . ( int ) $this->id;

							header('Location: ' . $this->view->route('admin/page/edit/' . ( int ) $this->id . '?path=' . rawurlencode($path) . '&notice=updated', FALSE));

							$this->app->end();
						}

						break;
					default:
						if ( $this->app->permission->check('admin page create') )
						{
							$nodeId = $this->app->node->create($this->app->input->POST_raw['title']['English US'], 'page', $this->app->input->POST_raw['parent']);

							if ( $nodeId )
							{
								$this->app->db->sql('
									UPDATE `' . $this->app->db->prefix . 'nodes` SET
										`home` =  ' . ( $this->app->input->POST_raw['home'] ? 1 : 0 ) . ',
										`path` = "' . $this->app->input->POST_db_safe['path']         . '"
									WHERE
										`id` = ' . ( $nodeId ) . '
									LIMIT 1
									;');

								$this->app->db->result = FALSE;

								foreach ( $languages as $language )
								{
									$this->app->db->sql('
										INSERT INTO `' . $this->app->db->prefix . 'pages` (
											`node_id`,
											`published`,
											`lang`,
											`date`,
											`date_edit`
											)
										VALUES (
											 ' . ( int ) $nodeId                                      . ',
											 ' . ( $this->app->input->POST_raw['published'] ? 1 : 0 ) . ',
											"' . $this->app->db->escape($language)                    . '",
											"' . gmdate('Y-m-d H:i:s')                                . '",
											"' . gmdate('Y-m-d H:i:s')                                . '"
											)
										;');

									if ( $pageId = $this->app->db->result )
									{
										$this->app->db->sql('
											INSERT INTO `' . $this->app->db->prefix . 'pages_revisions` (
												`page_id`,
												`title`,
												`body`,
												`date`
												)
											VALUES (
												 ' . $pageId                                             . ',
												"' . $this->app->input->POST_db_safe['title'][$language] . '",
												"' . $this->app->input->POST_db_safe['body'][$language]  . '",
												"' . gmdate('Y-m-d H:i:s')                               . '"
												)
											;');

										if ( $revisionId = $this->app->db->result )
										{
											$this->app->db->sql('
												UPDATE `' . $this->app->db->prefix . 'pages` SET
													`revision_id` =  ' . ( int ) $revisionId . '
												WHERE
													`id` =  ' . $pageId . '
												LIMIT 1
												;');
										}
									}
								}

								if ( $this->app->db->result )
								{
									$path = !empty($this->app->input->POST_raw['path']) ? $this->app->input->POST_raw['path'] : 'page/' . $nodeId;

									header('Location: ' . $this->view->route('admin/page/edit/' . $nodeId . '?path=' . rawurlencode($path) . '&notice=created', FALSE));

									exit;
								}
								else
								{
									$this->app->node->delete($nodeId);
								}
							}
					}
				}
			}
		}
		else if ( isset($this->app->input->GET_raw['notice']) )
		{
			$path = isset($this->app->input->GET_raw['path']) ? $this->app->input->GET_raw['path'] : '';

			switch ( $this->app->input->GET_raw['notice'] )
			{
				case 'created':
					$this->view->notice = $this->view->t('The page has been created (%1$sview%2$s).', array('<a href="' . $this->view->route($this->view->h($path)) . '">', '</a>'));

					break;
				case 'updated':
					$this->view->notice = $this->view->t('The page has been updated (%1$sview%2$s).', array('<a href="' . $this->view->route($this->view->h($path)) . '">', '</a>'));

					break;
				case 'deleted':
					$this->view->notice = $this->view->t('The page has been deleted.');

					break;
			}
		}

		switch ( $this->action )
		{
			case 'edit':
				$editLeftId  = 0;
				$editRightId = 0;

				if ( $this->app->permission->check('admin page edit') )
				{
					$node = $this->app->node->get_parents($this->id);

					if ( $node )
					{
						$this->app->db->sql('
							SELECT
								pr.`title`,
								pr.`body`,
								 p.`id`,
								 p.`revision_id`,
								 p.`published`,
								 p.`lang`,
								 n.`left_id`,
								 n.`right_id`,
								 n.`home`,
								 n.`path`
							FROM      `' . $this->app->db->prefix . 'nodes`           AS  n
							LEFT JOIN `' . $this->app->db->prefix . 'pages`           AS  p ON n.`id`          = p.`node_id`
							LEFT JOIN `' . $this->app->db->prefix . 'pages_revisions` AS pr ON p.`revision_id` = pr.`id`
							WHERE
								n.`id`= ' . ( int ) $this->id . '
							;');

						if ( $r = $this->app->db->result )
						{
							$editLeftId  = ( int ) $r[0]['left_id'];
							$editRightId = ( int ) $r[0]['right_id'];

							foreach ( $r as $d )
							{
								$this->app->input->POST_html_safe['title'][$d['lang']] = $d['title'];
								$this->app->input->POST_html_safe['body'][$d['lang']]  = $d['body'];
							}

							$this->app->input->POST_html_safe['parent']    = ( int ) $node['parents'][count($node['parents']) - 1]['id'];
							$this->app->input->POST_html_safe['published'] = $r[0]['published'] ? 1 : 0;
							$this->app->input->POST_html_safe['home']      = $r[0]['home']      ? 1 : 0;
							$this->app->input->POST_html_safe['path']      = $r[0]['path'];

							$path = $r[0]['path'];

							$this->app->db->sql('
								SELECT
									`id`,
									`date`,
									LENGTH(`title`) + LENGTH(`body`) AS bytes
								FROM `' . $this->app->db->prefix . 'pages_revisions`
								WHERE
									`page_id`  = ' . ( int ) $r[0]['id']          . ' AND
									`id`      != ' . ( int ) $r[0]['revision_id'] . '
								ORDER BY `date` DESC
								LIMIT 10
								;');

							$revisions = $this->app->db->result;
						}
					}
				}

				break;
			case 'delete':
				if ( $this->app->permission->check('admin page delete') )
				{
					if ( !$this->app->input->POST_valid['confirm'] )
					{
						$this->app->input->confirm($this->view->t('Are you sure you wish to permanently delete this page and all its revisions?'));
					}
					else
					{
						// Delete page
						if ( $this->app->node->delete($this->id) )
						{
							$this->app->db->sql('
								DELETE
									p, pr
								FROM      `' . $this->app->db->prefix . 'pages`           AS  p
								LEFT JOIN `' . $this->app->db->prefix . 'pages_revisions` AS pr ON p.`id` = pr.`page_id`
								WHERE
									`node_id` = ' . ( int ) $this->id . '
								;');

							if ( $this->app->db->result )
							{
								header('Location: ' . $this->view->route($this->path . '?notice=deleted', FALSE));

								$this->app->end();
							}
						}
					}
				}

				break;
			default:
				$this->app->input->POST_html_safe['published'] = 1;
		}

		foreach ( $languages as $language )
		{
			if ( !isset($this->app->input->POST_html_safe['title'][$language]) )
			{
				$this->app->input->POST_html_safe['title'][$language] = '';
			}

			if ( !isset($this->app->input->POST_html_safe['body'][$language]) )
			{
				$this->app->input->POST_html_safe['body'][$language] = '';
			}
		}

		$list        = array();
		$listParents = array();

		$nodes = $this->app->node->get_children(Node_Plugin::ROOT_ID, 'page');

		$this->app->node->nodes_to_array($nodes, $list);

		array_shift($list);

		foreach ( $list as $i => $item )
		{
			$list[$i]['level'] --;
		}

		$listParents = $list;

		// A page can not be a child of itself or its descendants, remove those pages from dropdown
		if ( $this->action == 'edit' )
		{
			foreach ( $listParents as $i => $d )
			{
				if ( $d['left_id'] >= $editLeftId && $d['right_id'] <= $editRightId )
				{
					unset($listParents[$i]);
				}
			}
		}

		$pagination = $this->view->paginate('pages', count($list), 10);

		$this->view->nodesParents    = $listParents;
		$this->view->nodes           = array_splice($list, $pagination['from'], 10);
		$this->view->nodesPagination = $pagination;
		$this->view->pagePath        = !empty($path)      ? $path      : 'page/' . $this->id;
		$this->view->revisions       = !empty($revisions) ? $revisions : array();

		$this->view->languages = $languages;

		$this->view->load('admin/page.html.php');
	}
}

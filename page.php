<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => './',
	'pageTitle' => 'Page'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'node', 'page'));

if ( isset($model->routeParts[1]) )
{
	$language = !empty($model->lang->ready) ? $model->lang->language : array('English US');

	$model->db->sql('
		SELECT
			p.`id`,
			p.`node_id`,
			p.`title`,
			p.`body`,
			n.`home`
		FROM      `' . $model->db->prefix . 'pages` AS p
		LEFT JOIN `' . $model->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
		WHERE
			n.`permalink` = "' . $model->db->escape($model->routeParts[1]) . '" AND
			p.`published` = 1 AND
			p.`lang`      = "' . $model->db->escape($language) . '"
		LIMIT 1
		;');

	if ( isset($model->db->result[0]) && $d = $model->db->result[0] )
	{
		$view->pageTitle = $d['title'];
		$view->nodeId    = $d['node_id'];
		$view->body      = $d['body'];
		$view->home      = $d['home'];

		/*
		 * Prefix relative links with the path to the root
		 * This way internal links won't break when the site
		 * is moved to another directory
		 */
		$model->page->parse_urls($view->body);

		/*
		 * Create a breadcrumb trail
		 */
		$view->parents = array();

		if ( !$d['home'] )
		{
			$nodes = $model->node->get_parents($d['node_id']);

			
			foreach ( $nodes['parents'] as $d )
			{
				if ( $d['id'] != node::rootId && $d['permalink'] != 'pages' )
				{
					$view->parents[$d['permalink']] = $d['title'];
				}
			}
		}
	}
}

if ( !isset($view->nodeId) )
{
	header('HTTP/1.0 404 Not Found');

	$view->pageTitle = $model->t('Page not found');
	$view->error     = $model->t('The page you are looking for does not exist.');
}

$view->load('page.html.php');

$model->end();

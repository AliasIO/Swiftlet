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

require($contrSetup['rootPath'] . 'init.php');

$app->check_dependencies(array('db', 'node', 'page'));

if ( isset($app->routeParts[1]) )
{
	$language = !empty($app->lang->ready) ? $app->lang->language : array('English US');

	$app->db->sql('
		SELECT
			p.`id`,
			p.`node_id`,
			p.`title`,
			p.`body`,
			n.`home`
		FROM      `' . $app->db->prefix . 'pages` AS p
		LEFT JOIN `' . $app->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
		WHERE
			n.`id`        = "' . ( int ) $app->routeParts[1] . '" AND
			n.`type`      = "page"                                  AND
			p.`published` = 1                                       AND
			p.`lang`      = "' . $app->db->escape($language) . '"
		LIMIT 1
		;');

	if ( isset($app->db->result[0]) && $d = $app->db->result[0] )
	{
		$view->pageTitle = $d['title'];
		$view->nodeId    = $d['node_id'];
		$view->body      = $view->allow_html($d['body']);
		$view->home      = $d['home'];

		/*
		 * Prefix relative links with the path to the root
		 * This way internal links won't break when the site
		 * is moved to another directory
		 */
		$app->page->parse_urls($view->body);

		/*
		 * Create a breadcrumb trail
		 */
		$view->parents = array();

		if ( !$d['home'] )
		{
			$nodes = $app->node->get_parents($d['node_id']);

			foreach ( $nodes['parents'] as $d )
			{
				if ( $d['id'] != node::rootId )
				{
					$view->parents[$d['path'] ? $d['path'] : 'node/' . $d['id']] = $d['title'];
				}
			}
		}
	}
}

if ( !isset($view->nodeId) )
{
	header('HTTP/1.0 404 Not Found');

	$view->pageTitle = $app->t('Page not found');
	$view->error     = $app->t('The page you are looking for does not exist.');
}

$view->load('page.html.php');

$app->end();

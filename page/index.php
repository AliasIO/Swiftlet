<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Page'
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'node', 'page'));

if ( isset($model->GET_raw['permalink']) )
{
	$language = !empty($model->lang->ready) ? $model->lang->language : array('English US');

	$model->db->sql('
		SELECT
			p.`id`,
			p.`node_id`,
			p.`title`,
			p.`body`
		FROM      `' . $model->db->prefix . 'pages` AS p
		LEFT JOIN `' . $model->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
		WHERE
			n.`permalink` = "' . $model->GET_db_safe['permalink'] . '" AND
			p.`lang`      = "' . $model->db->escape($language) . '"
		LIMIT 1
		;');

	if ( $model->db->result && $d = $model->db->result[0] )
	{
		$view->nodeId = $d['node_id'];
		$view->title  = $d['title'];
		$view->body   = $d['body'];

		// Replace relative paths with absolute paths to solve issues with the rich text editor
		preg_match_all('/(<[^<]*(src|href)=["\'])([^\/][^"\']+)/', $view->body, $m);

		if ( $m )
		{
			for ( $i = 0; $i < count($m[0]); $i ++ )
			{
				if ( !preg_match('/^[a-z]:\/\//i', $m[3][$i]) )
				{
					$view->body = str_replace($m[0][0], $m[1][0] . 'http://' . $_SERVER['SERVER_NAME'] . $contr->absPath . $m[3][0], $view->body);
				}
			}
		}

		$nodes = $model->node->get_parents($d['node_id']);

		$view->parents = array();

		foreach ( $nodes['parents'] as $d )
		{
			if ( $d['id'] != node::rootId && $d['permalink'] != 'pages' )
			{
				$view->parents[$d['permalink']] = $d['title'];
			}
		}
	}
	else
	{
		header('HTTP/1.0 404 Not Found'); 

		$view->error = 'The page you are looking for does not exist.';
	}
}

$view->load('page.html.php');

$model->end();
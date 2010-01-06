<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Page
 * @abstract
 */
class page
{
	public
		$ready
		;

	private
		$model,
		$contr
		;

	/**
	 * Initialize
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;

		if ( !empty($model->db->ready) )
		{
			/**
			 * Check if the pages table exists
			 */
			if ( in_array($model->db->prefix . 'pages', $model->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
	}

	/**
	 * Turn an array of nodes into a flat list
	 * @param array $nodes
	 * @param array $list
	 */
	function nodes_to_list($nodes, &$list)
	{
		foreach ( $nodes as $node )
		{
			$list[] = array(
				'id'        => $node['id'],
				'left_id'   => $node['left_id'],
				'right_id'  => $node['right_id'],
				'title'     => $node['title'],
				'permalink' => $node['permalink'],
				'level'     => $node['level']
				);

			if ( !empty($node['children']) )
			{
				$this->nodes_to_list($node['children'], $list);
			}
		}
	}

	/**
	 * Rewrite URLs
	 * @param string $url
	 * @return string
	 */
	function rewrite($url)
	{
		$url = preg_replace('/page\/\?permalink=(.+)?/', 'p/$1', $url);

		return $url;
	}

	/**
	 * Set the page title
	 */
	function set_page_title()
	{
		$model = $this->model;
		$contr = $this->contr;
		$view  = $this->model->view;

		if ( isset($model->GET_raw['permalink']) )
		{
			$model->db->sql('
				SELECT
					p.`id`,
					p.`node_id`,
					p.`body`,
					n.`title`
				FROM      `' . $model->db->prefix . 'pages` AS p
				LEFT JOIN `' . $model->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
				WHERE
					n.`permalink` = "' . $model->GET_db_safe['permalink'] . '"
				LIMIT 1
				;');

			if ( $model->db->result && $d = $model->db->result[0] )
			{
				$contr->pageTitle = $d['title'];
				$view->pageTitle  = $model->h($d['title']);
			}
		}
	}
}
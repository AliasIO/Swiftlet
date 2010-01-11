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

	/**
	 * Prefix relative URLs with path to root
	 * @param string $v
	 */
	function parse_urls(&$v)
	{
		$view = $this->model->view;

		preg_match_all('/(<[^<]*(src|href)=["\'])([^\/][^"\']+)/', $v, $m);

		if ( $m )
		{
			for ( $i = 0; $i < count($m[0]); $i ++ )
			{
				if ( !preg_match('/^[a-z]:\/\//i', $m[3][$i]) )
				{
					$v = str_replace($m[0][0], $m[1][0] . $view->rootPath . $m[3][0], $v);
				}
			}
		}
	}
}
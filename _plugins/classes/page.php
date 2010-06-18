<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this->model) ) die('Direct access to this file is not allowed');

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
		$view,
		$contr
		;

	/**
	 * Initialize
	 * @param object $this->model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->view  = $model->view;
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
		$url = preg_replace('/page\/\?page=(.+)?/', 'p/$1', $url);

		return $url;
	}

	/**
	 * Get route to home page
	 * @return string
	 */
	function get_home()
	{
		$this->model->db->sql('
			SELECT
				n.`permalink`
			FROM      `' . $this->model->db->prefix . 'pages` AS p
			LEFT JOIN `' . $this->model->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
			WHERE
				p.`published` = 1 AND
				n.`home`      = 1
			LIMIT 1
			;');

		if ( $r = $this->model->db->result )
		{
			return $this->rewrite('page/?page=' . $r[0]['permalink']);
		}
	}

	/**
	 * Prefix relative URLs with path to root
	 * @param string $v
	 */
	function parse_urls(&$v)
	{
		preg_match_all('/(<[^<]*(src|href)=["\'])([^\/][^"\']+)/', $v, $m);

		if ( $m )
		{
			for ( $i = 0; $i < count($m[0]); $i ++ )
			{
				if ( !preg_match('/^[a-z]+:\/\//i', $m[3][$i]) )
				{
					$v = str_replace($m[0][$i], $m[1][$i] . $this->view->rootPath . $m[3][$i], $v);
				}
			}
		}
	}
}

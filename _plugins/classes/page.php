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
		$url = preg_replace('/page\/\?page=(.+)?/', 'p/$1', $url);

		return $url;
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
				if ( !preg_match('/^[a-z]+:\/\//i', $m[3][$i]) )
				{
					$v = str_replace($m[0][$i], $m[1][$i] . $view->rootPath . $m[3][$i], $v);
				}
			}
		}
	}
}

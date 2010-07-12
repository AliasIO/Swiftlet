<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * View
 * @abstract
 */
class view
{
	public
		$rootPath,
		$viewPath,
		$siteName,
		$siteCopyright,
		$siteDesigner,
		$siteDescription,
		$siteKeywords,
		$pageTitle,
		$pageDescription,
		$pageKeywords,
		$inAdmin
		;

	private
		$app,
		$contr,

		$filesLoaded = array()
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app   = $app;
		$this->contr = $app->contr;

		$this->rootPath = $this->contr->absPath;
		$this->viewPath = $this->contr->absPath . '_view/';

		foreach ( array(
			'siteName',
			'siteCopyright',
			'siteDesigner',
			'siteDescription',
			'siteKeywords'
			) as $v )
		{
			$this->{$v} = !empty($app->{$v}) ? $app->h($app->{$v}) : '';
		}

		$this->pageTitle       = !empty($this->contr->pageTitle)       ? $app->h($this->contr->pageTitle)       : '';
		$this->pageDescription = !empty($this->contr->pageDescription) ? $app->h($this->contr->pageDescription) : $this->siteDescription;
		$this->pageKeywords    = !empty($this->contr->pageKeywords)    ? $app->h($this->contr->pageKeywords)    : $this->siteKeywords;
	}

	/*
	 * Load a view
	 * @param $file
	 */
	function load($file)
	{
		$this->filesLoaded[] = $file;
	}

	/*
	 * Output loaded views
	 */
	function output()
	{
		$app   = $this->app;
		$contr = $this->contr;
		$view  = $this;

		foreach ( $this->filesLoaded as $file )
		{
			if ( is_file($contr->viewPath . $file) )
			{
				require($contr->viewPath . $file);
			}
			else
			{
				$app->error(FALSE, 'Missing view file `' . $contr->viewPath . $file . '`.', __FILE__, __LINE__);
			}
		}
	}

	/*
	 * Allow HTML
	 * @param string $v
	 */
	function allow_html($v)
	{
		return html_entity_decode($v, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Create pagination
	 * @param string $id
	 * @param int $rows
	 * @param int $maxRows
	 * @return array
	 */
	function paginate($id, $rows, $maxRows, $exclude = array())
	{
		$pageNum = isset($this->app->GET_raw['p_' . $id]) ? $this->app->GET_raw['p_' . $id] : 1;

		$query = !empty($this->app->GET_raw) ? $this->app->GET_raw : array();

		unset($query['p_' . $id]);

		$params = '';

		if ( $query )
		{
			foreach ( $query as $k => $v )
			{
				if ( !in_array($k, $exclude) )
				{
					$params[] = $k . '=' . $v;
				}
			}
		}

		$url = '?' . ( $params ? implode('&', $params) . '&' : '' ) . 'p_' . $id . '=';

		$pages = ceil($rows / $maxRows);

		$pagination = '';

		if ( $pageNum > 1 )
		{
			$pagination = '<a class="pagination" href="' . $url . ( $pageNum - 1 ) . '#' . $id . '">' . $this->app->t('previous') . '</a> ';
		}

		for ( $i = 1; $i <= 3; $i ++ )
		{
			if ( $i > $pages )
			{
				break;
			}

			$pagination .= ( $i == $pageNum ? $i : '<a class="pagination" href="' . $url . $i . '#' . $id . '">' . $i . '</a>' ) . ' ';
		}


		if ( $pageNum > 7 )
		{
			$pagination .= '&hellip; ';
		}

		for ( $i = $pageNum - 3; $i <= $pageNum + 3; $i ++ )
		{
			if ( $i > 3 && $i < $pages - 2 )
			{
				$pagination .= ( $i == $pageNum ? $i : '<a class="pagination" href="' . $url . $i . '#' . $id . '">' . $i . '</a>' ) . ' ';
			}
		}

		if ( $pageNum < $pages - 6 )
		{
			$pagination .= '&hellip; ';
		}

		if ( $pages > 3 )
		{
			for ( $i = $pages - 2; $i <= $pages; $i ++)
			{
				if ( $i > 3 )
				{
					$pagination .= ( $i == $pageNum ? $i : '<a class="pagination" href="' . $url . $i . '#' . $id . '">' . $i . '</a>' ) . ' ';
				}
			}
		}

		if ( $pageNum < $pages )
		{
			$pagination .= '<a class="pagination" href="' . $url . ( $pageNum + 1 ) . '#' . $id . '">' . $this->app->t('next') . '</a> ';
		}

		$pagination = $this->app->t('Go to page') . ': ' . $pagination;

		return array('from' => ( $pageNum - 1 ) * $maxRows, 'to' => ( $pageNum * $maxRows < $rows ? ( $pageNum * $maxRows) : $rows ), 'html' => $pagination);
	}
}

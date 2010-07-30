<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($swiftlet) ) die('Direct access to this file is not allowed');

/**
 * View
 * @abstract
 */
class View
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
		$inAdmin,
		$action,
		$id,
		$route = array(
			'path'       => '',
			'args'       => array(),
			'controller' => 'Home',
			'action'     => '',
			'id'         => ''
			)
		;

	private
		$app,
		$controller,

		$filesLoaded = array()
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app = $app;

		if ( !empty($_GET['q']) )
		{
			$this->route['path']       = $_GET['q'];
			$this->route['args']       = explode('/', $_GET['q']);
			$this->route['controller'] = '';

			for ( $i = count($this->route['args']); $i > 0; $i -- )
			{
				$file = ucfirst(basename(implode('/', array_slice($this->route['args'], 0, $i))));

				if ( is_file('../_controllers/' . $file . '.php') )
				{
					$this->route['controller'] = $file;

					$this->route['action'] = isset($this->route['args'][$i])     ? $this->route['args'][$i]     : '';
					$this->route['id']     = isset($this->route['args'][$i + 1]) ? $this->route['args'][$i + 1] : '';
				}
			}
		}

		if ( empty($this->route['controller']) )
		{
			$this->route['controller'] = 'Err404';
		}

		array_shift($this->route['args']);

		$this->rootPath = str_repeat('../', count($this->route['args']));
		$this->viewPath = $this->rootPath . '_views/';
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
		$view       = $this;
		$app        = $this->app;
		$controller = $this->app->controller;

		foreach ( array(
			'siteName',
			'siteCopyright',
			'siteDesigner',
			'siteDescription',
			'siteKeywords'
			) as $v )
		{
			$this->{$v} = !empty($this->app->config[$v]) ? $this->h($this->app->config[$v]) : '';
		}

		$this->pageTitle       = !empty($this->app->controller->pageTitle)       ? $this->t($this->app->controller->pageTitle)       : '';
		$this->pageDescription = !empty($this->app->controller->pageDescription) ? $this->t($this->app->controller->pageDescription) : $this->siteDescription;
		$this->pageKeywords    = !empty($this->app->controller->pageKeywords)    ? $this->t($this->app->controller->pageKeywords)    : $this->siteKeywords;

		foreach ( $this->filesLoaded as $file )
		{
			if ( is_file('_views/' . $file) )
			{
				require('_views/' . $file);
			}
			else
			{
				$app->error(FALSE, 'Missing view file `' .$file . '`.', __FILE__, __LINE__);
			}
		}
	}

	/**
	 * Translate a string
	 * @param string $v
	 * @return string
	 */
	function t($v, $args = '')
	{
		$params = array(
			'string' => $this->h($v)
			);

		$this->app->hook('translate', $params);

		return $args ? ( is_array($args) ? vsprintf($params['string'], $args) : sprintf($params['string'], $args) ) : $v;
	}

	/**
	 * Convert special characters to HTML entities
	 * @param mixed $v
	 * @return mixed $v
	 */
	function h($v)
	{
		if ( is_array($v) )
		{
			return array_map(array($this, 'h'), $v);
		}
		else
		{
			return htmlentities($v, ENT_QUOTES, 'UTF-8');
		}
	}

	/*
	 * Allow HTML
	 * @param string $v
	 */
	static function allow_html($v)
	{
		return html_entity_decode($v, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Route URLs
	 * @param string $route
	 * @return string
	 */
	function route($route)
	{
		$route = $this->rootPath . ( $this->app->config['urlRewrite'] ? $route : '?q=' . str_replace('?', '&amp;', $route) );

		return $route;
	}

	/**
	 * Format a date
	 * @param string $v
	 * @return string
	 */
	function format_date($date, $type = 'datetime')
	{
		$params = array(
			'date' => $date,
			'type' => $type
			);

		$this->app->hook('format_date', $params);

		return $params['date'];
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
		$pageNum = isset($this->app->input->GET_raw['p_' . $id]) ? $this->app->input->GET_raw['p_' . $id] : 1;

		$query = !empty($this->app->input->GET_raw) ? $this->app->input->GET_raw : array();

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
			$pagination = '<a class="pagination" href="' . $url . ( $pageNum - 1 ) . '#' . $id . '">' . $this->t('previous') . '</a> ';
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
			$pagination .= '<a class="pagination" href="' . $url . ( $pageNum + 1 ) . '#' . $id . '">' . $this->t('next') . '</a> ';
		}

		$pagination = $this->t('Go to page') . ': ' . $pagination;

		return array('from' => ( $pageNum - 1 ) * $maxRows, 'to' => ( $pageNum * $maxRows < $rows ? ( $pageNum * $maxRows) : $rows ), 'html' => $pagination);
	}
}

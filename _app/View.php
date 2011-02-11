<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($swiftlet) ) die('Direct access to this file is not allowed');

/**
 * View
 * @abstract
 */
class View
{
	public
		$absPath,
		$action,
		$args       = array(),
		$controller = 'Home',
		$id,
		$inAdmin,
		$siteName,
		$siteCopyright,
		$siteDesigner,
		$siteDescription,
		$siteKeywords,
		$pageTitle,
		$pageDescription,
		$pageKeywords,
		$path,
		$request,
		$rootPath,
		$viewPath
		;

	private
		$app,
		$filesLoaded = array()
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app = $app;

		$args = array();

		if ( !empty($_GET['q']) )
		{
			$args = explode('/', $_GET['q']);

			$this->controller = '';

			for ( $i = count($args); $i > 0; $i -- )
			{
				if ( $i < count($args) )
				{
					$this->args = array_slice($args, $i, count($args));
				}

				$filename = ltrim(implode('/', array_slice($args, 0, $i - 1)) . '/' . ucfirst($args[$i - 1]), '/');

				if ( is_file('_controllers/' . $filename . '.php') )
				{
					$this->controller = $filename;
					$this->path       = implode('/', array_slice($args, 0, $i));

					break;
				}
			}
		}

		if ( empty($this->controller) )
		{
			$this->controller = 'Err404';
			$this->args       = $args;
		}

		$this->request = $this->path . ( $this->args ? ( $this->path ? '/' : '' ) . implode('/', $this->args) : '' );

		$this->action = $this->args           ?         $this->args[0] : '';
		$this->id     = isset($this->args[1]) ? ( int ) $this->args[1] : '';

		// Determine client-side path to root
		$level = count($args) > 1 && $this->app->config['urlRewrite'] ? count($args) - 1 : 0;
		$path  = dirname(preg_replace('/(.+?)\?.+$/', '$1', $_SERVER['REQUEST_URI']) . ' ') . '/';

		$this->app->debugOutput['request'] = array(
			'controller' => $this->controller,
			'request'    => $this->request,
			'path'       => $this->path,
			'action'     => $this->action,
			'id'         => $this->id,
			'arguments'  => $this->args
			);

		$this->absPath  = preg_replace('/([^\/]+\/){' . $level . '}$/', '', $path);
		$this->rootPath = $this->app->config['urlRewrite'] ? $this->absPath : './';
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

		return $args ? ( is_array($args) ? vsprintf($params['string'], $args) : sprintf($params['string'], $args) ) : $params['string'];
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
	 * @param bool $encode
	 * @return string
	 */
	function route($route, $encode = TRUE)
	{
		$route = $this->rootPath . ( $this->app->config['urlRewrite'] ? $route : '?q=' . str_replace('?', $encode ? '&amp;' : '&', $route) );

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

	function helper($helper, $params = array())
	{
		if ( !is_file($filename = '_views/_helpers/' . $helper . '.html.php') )
		{
			$this->app->error(FALSE, 'Helper `' . $this->h($helper) . '` doesn\'t exist (/' . $this->h($filename) . ').');
		}

		require($filename);
	}

	/**
	 * Create pagination
	 * @param string $name
	 * @param int $items
	 * @param int $show
	 * @return array
	 */
	function paginate($name, $items, $show)
	{
		$page = 1;

		foreach ( $this->args as $arg )
		{
			preg_match('/^' . preg_quote($name) . '\-page\-([0-9])+$/', $arg, $m);

			if ( !empty($m[1]) )
			{
				$page = $m[1];
			}
		}

		return array(
			'name'  => $name,
			'from'  => $show * ( $page - 1 ),
			'to'    => $show * $page < $items ? $show * $page : $items,
			'page'  => $page,
			'pages' => ceil($items / $show)
			);
	}
}

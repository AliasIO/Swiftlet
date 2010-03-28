<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

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
		$model,
		$contr,

		$filesLoaded = array()
		;

	/**
	 * Initialize
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;

		$model = $this->model;
		$contr = $this->contr;
		$view  = $this;

		$view->rootPath = $contr->absPath;
		$view->viewPath = $contr->absPath . '_view/';

		foreach ( array(
			'siteName',
			'siteCopyright',
			'siteDesigner',
			'siteDescription',
			'siteKeywords'
			) as $v )
		{
			$view->{$v} = !empty($model->{$v}) ? $model->h($model->{$v}) : '';
		}

		$view->pageTitle       = !empty($contr->pageTitle)       ? $model->h($contr->pageTitle)       : '';
		$view->pageDescription = !empty($contr->pageDescription) ? $model->h($contr->pageDescription) : $view->siteDescription;
		$view->pageKeywords    = !empty($contr->pageKeywords)    ? $model->h($contr->pageKeywords)    : $view->siteKeywords;
	}

	/*
	 * Load a View file
	 * @param $file
	 */
	function load($file)
	{
		$this->filesLoaded[] = $file;
	}

	/*
	 * Output loaded View files
	 */
	function output()
	{
		$model = $this->model;
		$contr = $this->contr;
		$view  = $this;

		/**
		 * Shorthand for $model->h()
		 * @param string $v
		 * @return string
		 */
		function h($v)
		{
			global $model;

			return $model->h($v);
		}

		/**
		 * Shorthand for $model->t()
		 * @param string $v
		 * @param mixed $args
		 * @return string
		 */
		function t($v, $args = '')
		{
			global $model;

			return $model->t($v, $args);
		}

		foreach ( $this->filesLoaded as $file )
		{
			if ( is_file($contr->viewPath . $file) )
			{
				require($contr->viewPath . $file);
			}
			else
			{
				$model->error(FALSE, 'Missing view file `' . $contr->viewPath . $file . '`.');
			}
		}
	}
}

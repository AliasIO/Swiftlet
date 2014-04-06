<?php

namespace Swiftlet\Abstracts;

require_once 'Swiftlet/Interfaces/Plugin.php';
require_once 'Swiftlet/Abstracts/Common.php';

/**
 * Plugin class
 * @abstract
 */
abstract class Plugin extends Common implements \Swiftlet\Interfaces\Plugin
{
	/**
	 * Application instance
	 * @var \Swiftlet\Interfaces\App
	 */
	protected $app;

	/**
	 * Controller instance
	 * @var \Swiftlet\Interfaces\Controller
	 */
	protected $controller;

	/**
	 * View instance
	 * @var \Swiftlet\Interfaces\View
	 */
	protected $view;

	/**
	 * Set application instance
	 * @param App $app
	 * @return View
	 */
	public function setApp(\Swiftlet\Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set controller instance
	 * @param Controller $controller
	 * @return Plugin
	 */
	public function setController(\Swiftlet\Interfaces\Controller $controller)
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * Set view instance
	 * @param View $view
	 * @return Plugin
	 */
	public function setView(\Swiftlet\Interfaces\View $view)
	{
		$this->view = $view;

		return $this;
	}
}

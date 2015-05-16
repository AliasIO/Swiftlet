<?php

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\App as AppInterface;
use \Swiftlet\Interfaces\Controller as ControllerInterface;
use \Swiftlet\Interfaces\Plugin as PluginInterface;
use \Swiftlet\Interfaces\View as ViewInterface;

/**
 * Plugin class
 * @abstract
 */
abstract class Plugin extends Common implements PluginInterface
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
	public function setApp(AppInterface $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set controller instance
	 * @param Controller $controller
	 * @return Plugin
	 */
	public function setController(ControllerInterface $controller)
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * Set view instance
	 * @param View $view
	 * @return Plugin
	 */
	public function setView(ViewInterface $view)
	{
		$this->view = $view;

		return $this;
	}
}

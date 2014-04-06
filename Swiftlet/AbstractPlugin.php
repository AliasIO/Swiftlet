<?php

namespace Swiftlet;

/**
 * Plugin class
 * @abstract
 */
abstract class AbstractPlugin extends AbstractCommon implements Interfaces\Plugin
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Controller instance
	 * @var Interfaces\Controller
	 */
	protected $controller;

	/**
	 * View instance
	 * @var Interfaces\View
	 */
	protected $view;

	/**
	 * Set application instance
	 * @param App $app
	 * @return View
	 */
	public function setApp(Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set controller instance
	 * @param Controller $controller
	 * @return Plugin
	 */
	public function setController(Interfaces\Controller $controller)
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * Set view instance
	 * @param View $view
	 * @return Plugin
	 */
	public function setView(Interfaces\View $view)
	{
		$this->view = $view;

		return $this;
	}
}

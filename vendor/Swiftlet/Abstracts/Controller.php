<?php

namespace Swiftlet\Abstracts;

/**
 * Controller class
 * @abstract
 */
abstract class Controller extends Common implements \Swiftlet\Interfaces\Controller
{
	/**
	 * Application instance
	 * @var \Swiftlet\Interfaces\App
	 */
	protected $app;

	/**
	 * View instance
	 * @var \Swiftlet\Interfaces\View
	 */
	protected $view;

	/**
	 * Page title
	 * @var string
	 */
	protected $title;

	/**
	 * Routes
	 * @var array
	 */
	protected $routes = array();

	/**
	 * Set application instance
	 * @param \Swiftlet\Interfaces\App $app
	 * @return \Swiftlet\Interfaces\Controller
	 */
	public function setApp(\Swiftlet\Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set view instance
	 * @param \Swiftlet\Interfaces\App $app
	 * @return \Swiftlet\Interfaces\Controller
	 */
	public function setView(\Swiftlet\Interfaces\View $view)
	{
		$this->view = $view;

		$reflection = new \ReflectionClass($this);

		$this->view->name = strtolower($reflection->getShortName());

		$this->view->pageTitle = $this->title;

		return $this;
	}

	/**
	 * Set page title
	 * @param string $app
	 * @return \Swiftlet\Interfaces\Controller
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		$this->view->pageTitle = $title;

		return $this;
	}

	/**
	 * Get routes
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Default action
	 */
	public function index()
	{ }
}

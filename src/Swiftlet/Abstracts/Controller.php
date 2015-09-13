<?php

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\App as AppInterface;
use \Swiftlet\Interfaces\Controller as ControllerInterface;
use \Swiftlet\Interfaces\View as ViewInterface;

/**
 * Controller class
 * @abstract
 */
abstract class Controller extends Common implements ControllerInterface
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
	 * Constructor
	 * @param \Swiftlet\Interfaces\App $App
	 * @param \Swiftlet\Interfaces\View $view
	 * @return Controller
	 */
	public function __construct(AppInterface $app, ViewInterface $view)
	{
		$this->app  = $app;
		$this->view = $view;

		$reflection = new \ReflectionClass($this);

		$this->view->name      = strtolower($reflection->getShortName());
		$this->view->pageTitle = $this->title;

		return $this;
	}

	/**
	 * Set page title
	 * @param string $title
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

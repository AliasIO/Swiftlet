<?php

namespace Bro\Abstracts;

use \Bro\Interfaces\App as AppInterface;
use \Bro\Interfaces\Controller as ControllerInterface;
use \Bro\Interfaces\View as ViewInterface;

/**
 * Controller class
 * @abstract
 */
abstract class Controller extends Common implements ControllerInterface
{
	/**
	 * Application instance
	 * @var \Bro\Interfaces\App
	 */
	protected $app;

	/**
	 * View instance
	 * @var \Bro\Interfaces\View
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
	 * @param \Bro\Interfaces\App $App
	 * @param \Bro\Interfaces\View $view
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
	 * @return \Bro\Interfaces\Controller
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

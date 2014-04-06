<?php

namespace Swiftlet;

/**
 * Controller class
 * @abstract
 */
abstract class AbstractController extends AbstractCommon implements Interfaces\Controller
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * View instance
	 * @var Interfaces\View
	 */
	protected $view;

	/**
	 * Page title
	 * @var string
	 */
	protected $title;

	/**
	 * Set application instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Controller
	 */
	public function setApp(Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set view instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Controller
	 */
	public function setView(Interfaces\View $view)
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
	 * @return Interfaces\Controller
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		$this->view->pageTitle = $title;

		return $this;
	}

	/**
	 * Default action
	 */
	public function index()
	{
	}
}

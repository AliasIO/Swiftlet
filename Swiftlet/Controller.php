<?php

namespace Swiftlet;

/**
 * Controller class
 * @abstract
 * @property Interfaces\App $app
 * @property Interfaces\View $view
 * @property string $title
 */
abstract class Controller extends Common implements Interfaces\Controller
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
	 * Constructor
	 * @param Interfaces\App $app
	 * @param Interfaces\View $view
	 */
	public function __construct(Interfaces\App $app, Interfaces\View $view)
	{
		$this->app  = $app;
		$this->view = $view;

		$this->view->set('pageTitle', $this->title);
	}

	/**
	 * Default action
	 */
	public function index()
	{
	}
}

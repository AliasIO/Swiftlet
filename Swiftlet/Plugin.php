<?php

namespace Swiftlet;

/**
 * Plugin class
 * @abstract
 * @property Interfaces\App $app
 * @property Interfaces\Controller $controller
 * @property Interfaces\View $view
 */
abstract class Plugin extends Common implements Interfaces\Plugin
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
	 * Constructor
	 * @param App $app
	 * @param View $view
	 * @param Controller $controller
	 */
	public function __construct(Interfaces\App $app, Interfaces\View $view, Interfaces\Controller $controller)
	{
		$this->app        = $app;
		$this->view       = $view;
		$this->controller = $controller;
	}
}

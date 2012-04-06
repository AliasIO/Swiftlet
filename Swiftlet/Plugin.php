<?php

namespace Swiftlet;

abstract class Plugin implements Interfaces\Plugin
{
	protected
		$app,
		$controller,
		$view
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
	 * @param object $controller
	 */
	public function __construct(Interfaces\App $app, Interfaces\View $view, Interfaces\Controller $controller)
	{
		$this->app        = $app;
		$this->view       = $view;
		$this->controller = $controller;
	}
}

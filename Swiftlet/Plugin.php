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
	public function __construct(App $app, View $view, Controller $controller)
   	{
		$this->app        = $app;
		$this->view       = $view;
		$this->controller = $controller;
	}
}

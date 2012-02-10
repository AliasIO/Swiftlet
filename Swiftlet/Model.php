<?php

namespace Swiftlet;

abstract class Model implements Interfaces\Model
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
	 */
	public function __construct(App $app, View $view, Controller $controller)
   	{
		$this->app        = $app;
		$this->view       = $view;
		$this->controller = $controller;
	}
}

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
	 * @param object $controller
	 */
	public function __construct(Interfaces\App $app, Interfaces\View $view, Interfaces\Controller $controller)
   	{
		$this->app        = $app;
		$this->view       = $view;
		$this->controller = $controller;
	}
}

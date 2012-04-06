<?php

namespace Swiftlet;

abstract class Controller implements Interfaces\Controller
{
	protected
		$app,
		$view,
		$title
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
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

	/**
	 * Fallback in case action doesn't exist
	 */
	public function notImplemented()
	{
		throw new \Exception('Action ' . $this->view->htmlEncode($this->app->getAction()) . ' not implemented in ' . get_called_class());
	}
}

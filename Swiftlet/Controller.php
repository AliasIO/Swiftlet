<?php

namespace Swiftlet;

abstract class Controller
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
	public function __construct(App $app, View $view)
   	{
		$this->app  = $app;
		$this->view = $view;

		$this->view->set('pageTitle', $this->title);
	}

	/**
	 * Get the page title
	 * @return string
	 */
	final public function getTitle()
   	{
		return $this->title;
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
		throw new \Exception('Action ' . View::htmlEncode($this->app->getAction()) . ' not implemented in ' . get_class($this->app->getController()));
	}
}

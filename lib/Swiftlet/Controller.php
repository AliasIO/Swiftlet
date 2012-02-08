<?php

namespace Swiftlet;

abstract class Controller
{
	protected
		$title
		;

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
		throw new \Exception('Action ' . View::htmlEncode(App::getAction()) . ' not implemented in ' . get_class(App::getController()));
	}
}

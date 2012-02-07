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
		throw new \Exception('Action not implemented in ' . __CLASS__);
	}
}

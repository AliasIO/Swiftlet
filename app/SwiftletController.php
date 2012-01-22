<?php

abstract class SwiftletController
{
	protected
		$_app,
		$_name,
		$_title
		;

	/**
	 * Initialize the controller
	 * @param object $app
	 * @param string $name
	 */
	public function __construct($app, $name)
	{
		$this->_app  = $app;
		$this->_name = $name;
	}

	/**
	 * Get the controller name
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the page title
	 * @return string
	 */
	public function getTitle()
   	{
		return $this->_title;
	}

	/**
	 * Default action
	 */
	public function indexAction()
   	{
	}

	/**
	 * Fallback in case action doesn't exist
	 * @return string
	 */
	public function notImplementedAction()
   	{
		throw new Exception('Action not implemented in ' . $this->_name);
	}
}

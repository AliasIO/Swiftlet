<?php

abstract class SwiftletController
{
	protected
		$_app,
		$_name,
		$_title
		;

	/**
	 * @param object $app
	 * @param string $name
	 */
	public function __construct($app, $name)
	{
		$this->_app  = $app;
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string
	 */
	public function getTitle()
   	{
		return $this->_title;
	}

	/**
	 *
	 */
	public function indexAction()
   	{
	}

	/**
	 * @return string
	 */
	public function notImplementedAction()
   	{
		throw new Exception('Action not implemented in ' . $this->_name);
	}
}

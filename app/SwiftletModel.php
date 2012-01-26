<?php

abstract class SwiftletModel
{
	protected
		$_app,
		$_name
		;

	/**
	 * Initialize the model
	 * @param object $app
	 * @param string $name
	 */
	public function __construct($app, $name)
	{
		$this->_app  = $app;
		$this->_name = $name;
	}

	/**
	 * Get the model name
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
}

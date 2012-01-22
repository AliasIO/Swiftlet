<?php

abstract class SwiftletPlugin
{
	protected
		$_app,
		$_name
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
}

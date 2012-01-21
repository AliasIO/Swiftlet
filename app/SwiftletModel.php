<?php

abstract class SwiftletModel
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
}

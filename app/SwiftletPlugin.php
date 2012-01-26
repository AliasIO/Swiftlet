<?php

abstract class SwiftletPlugin
{
	protected
		$_app,
		$_name
		;

	/**
	 * Initialize the plugin
	 * @param object $app
	 * @param string $name
	 */
	public function __construct($app, $name)
	{
		$this->_app  = $app;
		$this->_name = $name;
	}

	/**
	 * Get the plugin name
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
}

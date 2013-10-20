<?php

namespace Swiftlet;

/**
 * Library class
 * @abstract
 * @property Interfaces\Library $app
 */
abstract class Library extends Common implements Interfaces\Library
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Constructor
	 * @param Interfaces\App $app
	 */
	public function __construct(Interfaces\App $app)
	{
		$this->app = $app;
	}
}

<?php

namespace Swiftlet\Abstracts;

/**
 * Library class
 * @abstract
 */
abstract class Library extends Common implements \Swiftlet\Interfaces\Library
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Set application instance
	 * @param \Swiftlet\Interfaces\App $app
	 * @return \Swiftlet\Interfaces\Library
	 */
	public function setApp(\Swiftlet\Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}
}

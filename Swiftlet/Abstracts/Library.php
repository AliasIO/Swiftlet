<?php

namespace Swiftlet\Abstracts;

require_once 'Swiftlet/Interfaces/Library.php';
require_once 'Swiftlet/Abstracts/Common.php';

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

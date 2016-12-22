<?php

namespace Bro\Abstracts;

use \Bro\Interfaces\App as AppInterface;
use \Bro\Interfaces\Library as LibraryInterface;

/**
 * Library class
 * @abstract
 */
abstract class Library extends Common implements LibraryInterface
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Set application instance
	 * @param \Bro\Interfaces\App $app
	 * @return \Bro\Interfaces\Library
	 */
	public function setApp(AppInterface $app)
	{
		$this->app = $app;

		return $this;
	}
}

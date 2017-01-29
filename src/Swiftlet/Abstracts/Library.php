<?php

declare(strict_types=1);

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\{App as AppInterface, Library as LibraryInterface};

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
	 * @param \Swiftlet\Interfaces\App $app
	 * @return \Swiftlet\Interfaces\Library
	 */
	public function setApp(AppInterface $app): LibraryInterface
	{
		$this->app = $app;

		return $this;
	}
}

<?php

namespace Swiftlet;

/**
 * Library class
 * @abstract
 */
abstract class AbstractLibrary extends AbstractCommon implements Interfaces\Library
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Set application instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Library
	 */
	public function setApp(Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}
}

<?php

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\App as AppInterface;
use \Swiftlet\Interfaces\Controller as ControllerInterface;
use \Swiftlet\Interfaces\Listener as ListenerInterface;
use \Swiftlet\Interfaces\View as ViewInterface;

/**
 * Listener class
 * @abstract
 */
abstract class Listener extends Common implements ListenerInterface
{
	/**
	 * Application instance
	 * @var \Swiftlet\Interfaces\App
	 */
	protected $app;

	/**
	 * Set application instance
	 * @param App $app
	 * @return View
	 */
	public function setApp(AppInterface $app)
	{
		$this->app = $app;

		return $this;
	}
}

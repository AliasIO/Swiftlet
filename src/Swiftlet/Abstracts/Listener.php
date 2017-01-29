<?php

declare(strict_types=1);

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\{App as AppInterface, Controller as ControllerInterface, Listener as ListenerInterface, View as ViewInterface };

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
	public function setApp(AppInterface $app): ListenerInterface
	{
		$this->app = $app;

		return $this;
	}
}

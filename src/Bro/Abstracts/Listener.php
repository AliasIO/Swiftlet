<?php

namespace Bro\Abstracts;

use \Bro\Interfaces\App as AppInterface;
use \Bro\Interfaces\Controller as ControllerInterface;
use \Bro\Interfaces\Listener as ListenerInterface;
use \Bro\Interfaces\View as ViewInterface;

/**
 * Listener class
 * @abstract
 */
abstract class Listener extends Common implements ListenerInterface
{
	/**
	 * Application instance
	 * @var \Bro\Interfaces\App
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

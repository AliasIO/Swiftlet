<?php

namespace Swiftlet\Interfaces;

/**
 * Listener interface
 */
interface Listener extends Common
{
	/**
	 * Set application instance
	 * @param App $app
	 * @return Listener
	 */
	public function setApp(App $app);
}

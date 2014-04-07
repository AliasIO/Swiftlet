<?php

namespace Swiftlet\Interfaces;

/**
 * Model interface
 */
interface Model extends Common
{
	/**
	 * Set application instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Model
	 */
	public function setApp(App $app);
}

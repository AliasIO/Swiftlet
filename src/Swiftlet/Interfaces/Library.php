<?php

declare(strict_types=1);

namespace Swiftlet\Interfaces;

/**
 * Library interface
 */
interface Library extends Common
{
	/**
	 * Set application instance
	 * @param App $app
	 * @return Library
	 */
	public function setApp(App $app): Library;
}

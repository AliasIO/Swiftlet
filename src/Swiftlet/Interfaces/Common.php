<?php

declare(strict_types=1);

namespace Swiftlet\Interfaces;

/**
 * Common interface
 */
interface Common
{
	/**
	 * Getters and setters
	 *
	 * @param string $property
	 * @param mixed $arguments
	 * @throws Exception
	 */
	public function __call(string $property, array $arguments);
}

<?php

namespace Swiftlet\Interfaces;

/**
 * Common interface
 */
interface Common
{
	/**
	 * TODO
	 *
	 * @param string $property
	 * @param mixed $arguments
	 * @throws Exception
	 */
	public function __call($property, $arguments);
}

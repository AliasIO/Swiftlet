<?php

namespace Swiftlet\Interfaces;

/**
 * SwiftletObject interface
 */
interface SwiftletObject
{
	/**
	 *
	 */
	public function __call($property, $arguments);
}

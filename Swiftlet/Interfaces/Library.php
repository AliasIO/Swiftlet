<?php

namespace Swiftlet\Interfaces;

/**
 * Library interface
 */
interface Library extends SwiftletObject
{
	/**
	 * Constructor
	 * @param App $app
	 */
	public function __construct(App $app);
}

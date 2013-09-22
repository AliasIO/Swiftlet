<?php

namespace Swiftlet\Interfaces;

/**
 * Model interface
 */
interface Model extends SwiftletObject
{
	/**
	 * Constructor
	 * @param App $app
	 */
	public function __construct(App $app);
}

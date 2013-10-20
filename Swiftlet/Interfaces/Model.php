<?php

namespace Swiftlet\Interfaces;

/**
 * Model interface
 */
interface Model extends Common
{
	/**
	 * Constructor
	 * @param App $app
	 */
	public function __construct(App $app);
}

<?php

namespace Swiftlet\Interfaces;

/**
 * Library interface
 */
interface Library extends AbstractCommon
{
	/**
	 * Constructor
	 * @param App $app
	 */
	public function __construct(App $app);
}

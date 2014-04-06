<?php

namespace Swiftlet\Abstracts;

require_once 'Swiftlet/Interfaces/Model.php';
require_once 'Swiftlet/Abstracts/Common.php';

/**
 * Model class
 * @abstract
 */
abstract class Model extends Common implements \Swiftlet\Interfaces\Model
{
	/**
	 * Application instance
	 * @var AbstractInterfaces\App
	 */
	protected $app;

	/**
	 * Set application instance
	 * @param \Swiftlet\Interfaces\App $app
	 * @return \Swiftlet\Interfaces\Model
	 */
	public function setApp(\Swiftlet\Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}
}

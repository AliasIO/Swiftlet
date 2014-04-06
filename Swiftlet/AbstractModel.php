<?php

namespace Swiftlet;

/**
 * Model class
 * @abstract
 */
abstract class AbstractModel extends AbstractCommon implements Interfaces\Model
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Set application instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Model
	 */
	public function setApp(Interfaces\App $app)
	{
		$this->app = $app;

		return $this;
	}
}

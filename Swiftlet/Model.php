<?php

namespace Swiftlet;

/**
 * Model class
 * @abstract
 * @property Interfaces\App $app
 */
abstract class Model implements Interfaces\Model
{
	/**
	 * Application instance
	 * @var Interfaces\App
	 */
	protected $app;

	/**
	 * Constructor
	 * @param Interfaces\App $app
	 */
	public function __construct(Interfaces\App $app)
	{
		$this->app = $app;
	}
}

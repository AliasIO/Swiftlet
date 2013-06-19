<?php

namespace Swiftlet;

abstract class Model implements Interfaces\Model
{
	protected
		$app
		;

	/**
	 * Constructor
	 * @param Interfaces\App $app
	 */
	public function __construct(Interfaces\App $app)
	{
		$this->app = $app;
	}
}

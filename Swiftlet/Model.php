<?php

namespace Swiftlet;

abstract class Model implements Interfaces\Model
{
	protected
		$app
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
	 * @param object $controller
	 */
	public function __construct(Interfaces\App $app)
   	{
		$this->app = $app;
	}
}

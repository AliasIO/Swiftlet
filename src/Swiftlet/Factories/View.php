<?php

namespace Swiftlet\Factories;

use \Swiftlet\Abstracts\Factory as FactoryAbstract;

/**
 * View factory
 */
class View extends FactoryAbstract
{
	static function build()
	{
		return new \Swiftlet\View;
	}
}

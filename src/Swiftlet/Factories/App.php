<?php

namespace Swiftlet\Factories;

use \Swiftlet\Abstracts\Factory as FactoryAbstract;
use \Swiftlet\Interfaces\View as ViewInterface;

/**
 * Application factory
 */
class App extends FactoryAbstract
{
	static function build(ViewInterface $view, $vendor, $vendorPath = 'src/')
	{
		return new \Swiftlet\App($view, $vendor, $vendorPath);
	}
}

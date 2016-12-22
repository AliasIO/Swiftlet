<?php

namespace Bro\Factories;

use \Bro\Abstracts\Factory as FactoryAbstract;
use \Bro\Interfaces\View as ViewInterface;

/**
 * Application factory
 */
class App extends FactoryAbstract
{
	static function build(ViewInterface $view, $vendor, $vendorPath = 'src/')
	{
		return new \Bro\App($view, $vendor, $vendorPath);
	}
}

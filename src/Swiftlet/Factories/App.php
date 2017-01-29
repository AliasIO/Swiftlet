<?php

declare(strict_types=1);

namespace Swiftlet\Factories;

use \Swiftlet\Abstracts\Factory as FactoryAbstract;
use \Swiftlet\Interfaces\{App as AppInterface, View as ViewInterface};

/**
 * Application factory
 */
class App extends FactoryAbstract
{
	static function build(ViewInterface $view, string $vendor, string $vendorPath = 'src/'): AppInterface
	{
		return new \Swiftlet\App($view, $vendor, $vendorPath);
	}
}

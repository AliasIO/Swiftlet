<?php

declare(strict_types=1);

namespace Swiftlet\Factories;

use \Swiftlet\Abstracts\Factory as FactoryAbstract;
use \Swiftlet\Interfaces\{App as AppInterface, Controller as ControllerInterface, View as ViewInterface};

/**
 * Controller factory
 */
class Controller extends FactoryAbstract
{
	static public $vendor = 'Swiftlet';

	static public $vendorPath = 'src/';

	static public function build(string $controllerName, AppInterface $app, ViewInterface $view): ControllerInterface
	{
		$prefix = '\\' . $app->getVendor() . '\\Controllers\\';

		$controllerClass = $prefix . ucfirst($controllerName);

		if ( !is_file($app->getVendorPath() . str_replace('\\', '/', $controllerClass) . '.php') ) {
			$controllerClass = $prefix . 'Error404';
		}

		return new $controllerClass($app, $view);
	}
}

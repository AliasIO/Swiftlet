<?php

declare(strict_types=1);

namespace Swiftlet\Factories;

use \Swiftlet\Abstracts\Factory as FactoryAbstract;
use \Swiftlet\Abstracts\View as ViewAbstract;

/**
 * View factory
 */
class View extends FactoryAbstract
{
	static function build(): ViewAbstract
	{
		return new \Swiftlet\View;
	}
}

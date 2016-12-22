<?php

namespace Bro\Factories;

use \Bro\Abstracts\Factory as FactoryAbstract;

/**
 * View factory
 */
class View extends FactoryAbstract
{
	static function build()
	{
		return new \Bro\View;
	}
}

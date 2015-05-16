<?php

namespace HelloWorld\Models;

use \Swiftlet\Abstracts\Model as ModelAbstract;

/**
 * Example model
 */
class Example extends ModelAbstract
{
	/**
	 * Example method. This could be anything, really...
	 * @return string
	 */
	public function getHelloWorld()
	{
		return 'Hello, world!';
	}
}

<?php

namespace HelloWorld\Controllers;

use \Swiftlet\Abstracts\Controller as ControllerAbstract;

/**
 * Error 404 controller
 */
class Error404 extends ControllerAbstract
{
	/**
	 * Page title
	 * @var string
	 */
	protected $title = 'Error 404';

	/**
	 * Default action
	 */
	public function index()
	{
		if ( !headers_sent() ) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
		}
	}
}

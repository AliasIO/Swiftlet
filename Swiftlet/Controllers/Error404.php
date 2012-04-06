<?php

namespace Swiftlet\Controllers;

class Error404 extends \Swiftlet\Controller
{
	protected
		$title = 'Error 404'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
	}

	/**
	 * Not implemented action
	 */
	public function notImplemented()
	{
		$this->index();
	}
}

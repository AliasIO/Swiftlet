<?php

namespace Swiftlet\Controllers;

class Error404 extends \Swiftlet\Controller
{
	protected
		$_title = 'Error 404'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		header('Status: 404 Not Found');
	}

	/**
	 * Not implemented action
	 */
	public function notImplemented()
   	{
		$this->indexAction();
	}
}

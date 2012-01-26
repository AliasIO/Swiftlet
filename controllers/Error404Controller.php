<?php

class Error404Controller extends SwiftletController
{
	protected
		$_title = 'Error 404'
		;

	/**
	 * Default action
	 */
	public function indexAction()
	{
		header('Status: 404 Not Found');
	}

	/**
	 * Not implemented action
	 */
	public function notImplementedAction()
   	{
		$this->indexAction();
	}
}

<?php

class Error404Controller extends SwiftletController
{
	protected
		$_title = 'Error 404'
		;

	public function indexAction()
	{
		header("Status: 404 Not Found");
	}
}

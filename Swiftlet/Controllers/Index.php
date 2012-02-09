<?php

namespace Swiftlet\Controllers;

use
	Swiftlet\App,
	Swiftlet\View
	;

class Index extends \Swiftlet\Controller
{
	protected
		$title = 'Home'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		// Some example code to get you started

		// Create a model instance, see /models/ExampleModel.php
		$exampleModel = $this->app->getModel('example');

		// Get some data from the model
		$helloWorld = $exampleModel->getHelloWorld();

		// Pass the data to the view to display it
		$this->view->set('helloWorld', $helloWorld);
	}
}


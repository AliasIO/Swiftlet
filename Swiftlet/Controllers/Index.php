<?php

namespace Swiftlet\Controllers;

/**
 * Index controller
 */
class Index extends \Swiftlet\Controller
{
	/**
	 * Page title
	 * @var string
	 */
	protected $title = 'Home';

	/**
	 * Default action
	 */
	public function index()
	{
		// Some example code to get you started

		// Create a model instance, see /Swiftlet/Models/Example.php
		$exampleModel = $this->app->getModel('example');

		// Get some data from the model and pass it to the view to display it
		$this->view->helloWorld = $exampleModel->getHelloWorld();
	}
}

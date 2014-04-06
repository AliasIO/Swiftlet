<?php

namespace Swiftlet\Controllers;

/**
 * Index controller
 */
class Index extends \Swiftlet\AbstractController
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
		$example = new \Swiftlet\Models\Example;

		// Get some data from the model and pass it to the view to display it
		$this->view->helloWorld = $example->getHelloWorld();
	}
}

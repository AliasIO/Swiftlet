<?php

namespace HelloWorld\Controllers;

use \HelloWorld\Models\Example as ExampleModel;
use \Swiftlet\Abstracts\Controller as ControllerAbstract;

/**
 * Index controller
 */
class Index extends ControllerAbstract
{
	/**
	 * Page title
	 * @var string
	 */
	protected $title = 'Hello, world!';

	/**
	 * Default action
	 * @param $args array
	 */
	public function index(array $args = array())
	{
		// Create a model instance, see /src/HelloWorld/Models/Example.php
		$example = new ExampleModel;

		// Get some data from the model and pass it to the view to display it
		$this->view->helloWorld = $example->getHelloWorld();
	}
}

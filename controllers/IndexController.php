<?php

namespace Swiftlet;

class IndexController extends Controller
{
	protected
		$_title = 'Home'
		;

	/**
	 * Default action
	 */
	public function indexAction()
	{
		// Some example code to get you started

		// Create a model instance, see /models/ExampleModel.php
		$exampleModel = App::getModel('example');

		// Get some data from the model
		$helloWorld = $exampleModel->getHelloWorld();

		// Pass the data to the view to display it
		View::set('hello world', $helloWorld);
	}
}


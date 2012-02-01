<?php

namespace Swiftlet;

class IndexController extends Controller
{
	protected
		$_title = 'Home'
		;

	/**
	 *
	 */
	public function indexAction()
	{
		$exampleModel = App::getModel('example');

		$helloWorld = $exampleModel->getHelloWorld();

		View::set('hello world', $helloWorld);
	}
}

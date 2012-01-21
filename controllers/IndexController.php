<?php

class IndexController extends SwiftletController
{
	protected
		$_title = 'Home'
		;

	/**
	 *
	 */
	public function indexAction()
	{
		$exampleModel = $this->_app->getModel('example');

		$helloWorld = $exampleModel->getHelloWorld();

		$this->_app->getView()->set('hello world', $helloWorld);
	}
}

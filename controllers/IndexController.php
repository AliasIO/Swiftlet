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
		$exampleModel = Swiftlet::getModel('example');

		$helloWorld = $exampleModel->getHelloWorld();

		SwiftletView::set('hello world', $helloWorld);
	}
}

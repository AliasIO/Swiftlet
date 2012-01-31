<?php

class ExamplePlugin extends SwiftletPlugin
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfterHook()
   	{
		if ( get_class(Swiftlet::getController()) === 'IndexController' ) {
			$helloWorld = SwiftletView::get('hello world');

			SwiftletView::set('hello world', $helloWorld . ' This string was altered by ' . __CLASS__ . '.');
		}
	}
}

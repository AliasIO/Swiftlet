<?php

namespace Swiftlet;

class ExamplePlugin extends Plugin
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfterHook()
   	{
		if ( get_class(App::getController()) === 'IndexController' ) {
			$helloWorld = View::get('hello world');

			View::set('hello world', $helloWorld . ' This string was altered by ' . __CLASS__ . '.');
		}
	}
}

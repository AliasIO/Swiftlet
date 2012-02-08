<?php

namespace Swiftlet\Plugins;

use
	Swiftlet\App,
	Swiftlet\View
	;

class Example extends \Swiftlet\Plugin
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfter()
   	{
		if ( get_class(App::getController()) === 'Swiftlet\Controllers\Index' ) {
			$helloWorld = View::get('hello world');

			View::set('hello world', $helloWorld . ' This string was altered by ' . __CLASS__ . '.');
		}
	}
}

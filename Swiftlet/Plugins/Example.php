<?php

namespace Swiftlet\Plugins;

class Example extends \Swiftlet\Plugin
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfter()
	{
		if ( $this->app->getControllerName() === 'Index' ) {
			$this->view->helloWorld .= ' This string was altered by ' . __CLASS__ . '.';
		}
	}
}

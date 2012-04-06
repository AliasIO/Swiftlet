<?php

namespace Swiftlet\Plugins;

class Example extends \Swiftlet\Plugin
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfter()
	{
		if ( get_class($this->controller) === 'Swiftlet\Controllers\Index' ) {
			$helloWorld = $this->view->get('helloWorld');

			$this->view->set('helloWorld', $helloWorld . ' This string was altered by ' . __CLASS__ . '.');
		}
	}
}

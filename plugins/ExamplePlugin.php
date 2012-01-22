<?php

class ExamplePlugin extends SwiftletPlugin
{
	/**
	 *
	 */
	public function actionAfterHook() {
		if ( $this->_app->getController()->getName() === 'IndexController' ) {
			$helloWorld = $this->_app->getView()->get('hello world');

			$this->_app->getView()->set('hello world', $helloWorld . ' This string was altered by ' . $this->_name . '.');
		}
	}
}

<?php

namespace Swiftlet\Plugins;

/**
 * Example plugin
 */
class Example extends \Swiftlet\AbstractPlugin
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfter()
	{
		$this->view->helloWorld .= ' This string was altered by ' . __CLASS__ . '.';
	}
}

<?php

namespace HelloWorld\Plugins;

use \Swiftlet\Abstracts\Plugin as PluginAbstract;

/**
 * Example plugin
 */
class Example extends PluginAbstract
{
	/**
	 * Implementation of the actionAfter hook
	 */
	public function actionAfter()
	{
		$this->view->helloWorld .= ' This string was altered by ' . __CLASS__ . '.';
	}
}

<?php

namespace HelloWorld\Listeners;

use \Bro\Abstracts\Controller as ControllerAbstract;
use \Bro\Abstracts\Listener as ListenerAbstract;
use \Bro\Abstracts\View as ViewAbstract;

/**
 * Example plugin
 */
class Example extends ListenerAbstract
{
	/**
	 * actionAfter event listener
	 */
	public function actionAfter(ControllerAbstract $controller, ViewAbstract $view)
	{
		$view->helloWorld .= ' This string was altered by ' . __CLASS__ . '.';
	}
}

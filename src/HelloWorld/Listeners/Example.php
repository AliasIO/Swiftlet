<?php

declare(strict_types=1);

namespace HelloWorld\Listeners;

use \Swiftlet\Abstracts\{Controller as ControllerAbstract, Listener as ListenerAbstract, View as ViewAbstract};

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

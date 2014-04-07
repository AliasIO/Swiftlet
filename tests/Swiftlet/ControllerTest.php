<?php

namespace Swiftlet;

$dir = dirname(__FILE__) . '/../../vendor/Swiftlet/';

require_once $dir . 'Interfaces/Common.php';
require_once $dir . 'Interfaces/App.php';
require_once $dir . 'Interfaces/Controller.php';
require_once $dir . 'Interfaces/View.php';
require_once $dir . 'Abstracts/Common.php';
require_once $dir . 'Abstracts/App.php';
require_once $dir . 'Abstracts/Controller.php';
require_once $dir . 'Abstracts/View.php';

require_once 'Mocks/App.php';
require_once 'Mocks/Controller.php';
require_once 'Mocks/View.php';

class ControllerTest extends \PHPUnit_Framework_TestCase
{
	public function testSetApp()
	{
		$app        = new Mocks\App;
		$controller = new Mocks\Controller;

		$this->assertEquals($controller->setApp($app), $controller);
	}

	public function testSetView()
	{
		$view       = new Mocks\View;
		$controller = new Mocks\Controller;

		$this->assertEquals($controller->setView($view), $controller);
	}

	public function testSetTitle()
	{
		$view       = new Mocks\View;
		$controller = new Mocks\Controller;

		$controller->setView($view);

		$this->assertEquals($controller->setTitle('title'), $controller);
	}
}

<?php

namespace Swiftlet;

require 'AppMock.php';
require 'ControllerMock.php';
require 'ViewMock.php';

class ControllerTest extends \PHPUnit_Framework_TestCase
{
	public function testSetApp()
	{
		$app        = new AppMock;
		$controller = new ControllerMock;

		$this->assertEquals($controller->setApp($app), $controller);
	}

	public function testSetView()
	{
		$view       = new ViewMock;
		$controller = new ControllerMock;

		$this->assertEquals($controller->setView($view), $controller);
	}

	public function testSetTitle()
	{
		$view       = new ViewMock;
		$controller = new ControllerMock;

		$controller->setView($view);

		$this->assertEquals($controller->setTitle('title'), $controller);
	}
}

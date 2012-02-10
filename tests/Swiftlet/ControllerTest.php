<?php

namespace Swiftlet;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
	protected
		$app,
		$controller
		;

	public function setUp()
	{
		$this->app = new App;

		$this->controller = $this->app->controller;
	}

	public function testController()
	{
		$this->assertInternalType('object', $this->controller);

		$this->assertInstanceOf('Swiftlet\Controller', $this->controller);

		$controllerName = get_class($this->controller);

		$this->assertEquals('Swiftlet\Controllers\Index', $controllerName);
	}
}

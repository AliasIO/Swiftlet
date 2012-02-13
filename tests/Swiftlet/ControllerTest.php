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

		set_error_handler(array($this->app, 'error'), E_ALL | E_STRICT);

		spl_autoload_register(array($this->app, 'autoload'));

		list(, $this->controller) = $this->app->run();
	}

	public function testController()
	{
		$this->assertInternalType('object', $this->controller);

		$this->assertInstanceOf('Swiftlet\Controller', $this->controller);

		$controllerName = get_class($this->controller);

		$this->assertEquals('Swiftlet\Controllers\Index', $controllerName);
	}
}

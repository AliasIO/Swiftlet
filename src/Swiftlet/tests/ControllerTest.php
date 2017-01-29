<?php

namespace Mock;

require_once 'vendor/autoload.php';

use \Mock\Controllers\Index as IndexController;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
	protected $controller;

	protected $view;

	protected function setUp()
	{
		$this->view       = new View;
		$this->app        = new App($this->view, 'Mock');
		$this->controller = new IndexController($this->app, $this->view);
	}

	public function testSetTitle()
	{
		$this->assertEquals($this->controller->setTitle('title'), $this->controller);
	}

	public function testGetRoutes()
	{
		$this->assertInternalType('array', $this->controller->getRoutes());
	}
}

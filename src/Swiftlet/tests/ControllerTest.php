<?php

namespace Mock;

require_once 'vendor/autoload.php';

use \Mock\Controllers\Index as IndexController;
use \PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
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

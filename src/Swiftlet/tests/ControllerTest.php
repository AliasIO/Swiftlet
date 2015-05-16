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
		$this->controller = new IndexController;
	}

	public function testSetApp()
	{
		$app = new App($this->view, 'Mock');

		$this->assertEquals($this->controller->setApp($app), $this->controller);
	}

	public function testSetView()
	{
		$this->assertEquals($this->controller->setView($this->view), $this->controller);
	}

	public function testSetTitle()
	{
		$this->controller->setView($this->view);

		$this->assertEquals($this->controller->setTitle('title'), $this->controller);
	}
}

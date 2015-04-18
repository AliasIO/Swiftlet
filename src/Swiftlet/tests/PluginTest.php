<?php

namespace Mock;

require_once 'vendor/autoload.php';

class PluginTest extends \PHPUnit_Framework_TestCase
{
	protected $plugin;

	protected function setUp()
	{
		$this->plugin = new Plugins\Mock;
	}

	function testSetApp()
	{
		$app = new App(new View, 'Mock', __DIR__);

		$this->assertEquals($this->plugin->setApp($app), $this->plugin);
	}

	function testSetController()
	{
		$controller = new Controllers\Index;

		$this->assertEquals($this->plugin->setController($controller), $this->plugin);
	}

	function testSetView()
	{
		$view = new View;

		$this->assertEquals($this->plugin->setView($view), $this->plugin);
	}
}

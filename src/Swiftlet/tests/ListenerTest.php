<?php

namespace Mock;

require_once 'vendor/autoload.php';

class ListenerTest extends \PHPUnit_Framework_TestCase
{
	protected $listener;

	protected function setUp()
	{
		$this->listener = new Listeners\Mock;
	}

	function testSetApp()
	{
		$app = new App(new View, 'Mock', __DIR__);

		$this->assertEquals($this->listener->setApp($app), $this->listener);
	}
}

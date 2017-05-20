<?php

namespace Mock;

use \PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

class ListenerTest extends TestCase
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

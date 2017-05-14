<?php

namespace Mock;

require_once 'vendor/autoload.php';

use \Mock\Controllers\Index as IndexController;
use \PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
	protected $app;

	protected $view;

	protected function setUp()
	{
		$this->view = new View;

		$this->app = new App($this->view, 'Mock');

		set_error_handler([ $this->app, 'error' ], E_ALL | E_STRICT);

		date_default_timezone_set('UTC');
	}

	function testDispatchController()
	{
		$this->assertEquals($this->app->dispatchController(), $this->app);
	}

	function testGetArgs()
	{
		$this->assertInternalType('array', $this->app->getArgs());
	}

	function testLoadListeners()
	{
		$this->assertEquals($this->app->loadListeners(), $this->app);
	}

	function testSetGetConfig()
	{
		$this->assertEquals($this->app->setConfig('key', 'value'), $this->app);
		$this->assertEquals($this->app->getConfig('key'), 'value');
	}

	function testTrigger()
	{
		$this->assertEquals($this->app->trigger('test'), $this->app);
	}

	/**
	 * @expectedException \ErrorException
	 */
	function testError()
	{
		$this->app->error(0, '', '', 0);
	}
}

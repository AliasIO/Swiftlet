<?php

namespace Mock;

require_once 'vendor/autoload.php';

use \Mock\Libraries\Mock as MockLibrary;

class LibraryTest extends \PHPUnit_Framework_TestCase
{
	protected $library;

	protected function setUp()
	{
		$this->library = new MockLibrary;
	}

	function testSetApp()
	{
		$app = new App(new View, 'Mock', __DIR__);

		$this->assertEquals($this->library->setApp($app), $this->library);
	}
}

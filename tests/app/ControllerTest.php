<?php

namespace Swiftlet;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers Swiftlet\Controller::getTitle
	 */
	public function testGetTitle()
	{
		$title = App::getController()->getTitle();

		$this->assertEquals('Home', $title);
	}
}

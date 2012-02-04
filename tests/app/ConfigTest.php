<?php

namespace Swiftlet;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers Swiftlet\Config::get
	 */
	public function testGet()
	{
		$test = Config::get('test');

		$this->assertEmpty($test);
	}

	/**
	 * @covers Swiftlet\Config::set
	 */
	public function testSet()
	{
		Config::set('test', 'test');

		$test = Config::get('test');

		$this->assertEquals('test', $test);
	}
}

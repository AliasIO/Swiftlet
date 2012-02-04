<?php

namespace Swiftlet;

class ViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers Swiftlet\View::getTitle
	 */
	public function testGetTitle()
	{
		$title = View::getTitle();

		$this->assertEquals('Home', $title);
	}

	/**
	 * @covers Swiftlet\View::get
	 */
	public function testGet()
	{
		$test = View::get('test');

		$this->assertEmpty($test);
	}

	/**
	 * @covers Swiftlet\View::set
	 */
	public function testSet()
	{
		View::set('test', 'test');

		$test = View::get('test');

		$this->assertEquals('test', $test);
	}

	/**
	 * @covers Swiftlet\View::htmlEncode
	 */
	public function testHtmlEncode()
	{
		$value = View::htmlEncode('&');

		$this->assertEquals('&amp;', $value);
	}

	/**
	 * @covers Swiftlet\View::htmlDecode
	 */
	public function testHtmlDecode()
	{
		$value = View::htmlDecode('&amp;');

		$this->assertEquals('&', $value);
	}
}

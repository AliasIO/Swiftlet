<?php

namespace Swiftlet;

class ViewTest extends \PHPUnit_Framework_TestCase
{
	protected
		$app,
		$view
		;

	public function setUp()
	{
		$this->app = new App;

		set_error_handler(array($this->app, 'error'), E_ALL | E_STRICT);

		spl_autoload_register(array($this->app, 'autoload'));

		list($this->view) = $this->app->run();
	}

	public function testView()
	{
		$this->assertInternalType('object', $this->view);

		$this->assertInstanceOf('Swiftlet\View', $this->view);
	}

	/**
	 * @covers Swiftlet\View::get
	 */
	public function testGet()
	{
		$test = $this->view->get('test');

		$this->assertNull($test);
	}

	/**
	 * @covers Swiftlet\View::set
	 */
	public function testSet()
	{
		$this->view->set('test', 'test');

		$test = $this->view->get('test');

		$this->assertEquals('test', $test);
	}

	/**
	 * @covers Swiftlet\View::htmlEncode
	 */
	public function testHtmlEncode()
	{
		$value = $this->view->htmlEncode('&');

		$this->assertEquals('&amp;', $value);
	}

	/**
	 * @covers Swiftlet\View::htmlDecode
	 */
	public function testHtmlDecode()
	{
		$value = $this->view->htmlDecode('&amp;');

		$this->assertEquals('&', $value);
	}
}

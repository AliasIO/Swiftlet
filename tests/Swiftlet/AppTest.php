<?php

namespace Swiftlet;

require_once(dirname(__FILE__) . '/../../Swiftlet/App.php');

spl_autoload_register(array('Swiftlet\App', 'autoload'));

class AppTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers Swiftlet\App::run
	 */
	public function testRun()
	{
		$running = App::run();

		$this->assertTrue($running);
	}

	/**
	 * @covers Swiftlet\App::getAction
	 */
	public function testGetAction()
	{
		$action = App::getAction();

		$this->assertEquals($action, 'index');
	}

	/**
	 * @covers Swiftlet\App::getArgs
	 */
	public function testGetArgs()
	{
		$args = App::getArgs();

		$this->assertInternalType('array', $args);

		$this->assertEmpty($args);
	}

	/**
	 * @covers Swiftlet\App::getModel
	 */
	public function testGetModel()
	{
		$model = App::getModel('example');

		$this->assertInternalType('object', $model);

		$this->assertInstanceOf('Swiftlet\Model',          $model);
		$this->assertInstanceOf('Swiftlet\Models\Example', $model);

		$this->assertEquals(get_class($model), 'Swiftlet\Models\Example');
	}

	/**
	 * @covers Swiftlet\App::getSingleton
	 */
	public function testGetSingleton()
	{
		$model  = App::getSingleton('example');
		$model2 = App::getSingleton('example');

		$this->assertInternalType('object', $model);

		$this->assertInstanceOf('Swiftlet\Model',          $model);
		$this->assertInstanceOf('Swiftlet\Models\Example', $model);

		$this->assertEquals(get_class($model), 'Swiftlet\Models\Example');

		$model->test = 'test';

		$this->assertSame($model, $model2);
	}

	/**
	 * @covers Swiftlet\App::getView
	 */
	public function testGetView()
	{
		$view = App::getView();

		$this->assertEquals('index', $view);
	}

	/**
	 * @covers Swiftlet\App::setView
	 */
	public function testSetView()
	{
		App::setView('test');

		$view = App::getView();

		$this->assertEquals('test', $view);
	}

	/**
	 * @covers Swiftlet\App::getController
	 */
	public function testGetController()
	{
		$controller = App::getController();

		$this->assertInternalType('object', $controller);

		$this->assertInstanceOf('Swiftlet\Controller',        $controller);
		$this->assertInstanceOf('Swiftlet\Controllers\Index', $controller);

		$this->assertEquals(get_class($controller), 'Swiftlet\Controllers\Index');

		$title = $controller->getTitle();

		$this->assertEquals('Home', $title);
	}

	/**
	 * @covers Swiftlet\App::getRootPath
	 */
	public function testGetRootPath()
	{
		$rootPath = App::getRootPath();

		$this->assertEquals('/', $rootPath);
	}

	/**
	 * @covers Swiftlet\App::registerHook
	 */
	public function testRegisterHook()
	{
		App::registerHook('test', array());

		$hooks = App::getHooks();

		$this->assertContains('test', $hooks);
	}
}

<?php

namespace Swiftlet;

$path = dirname(__FILE__) . '/../../';

require_once($path . 'app/App.php');
require_once($path . 'app/Model.php');
require_once($path . 'app/View.php');
require_once($path . 'app/Controller.php');
require_once($path . 'app/Plugin.php');

class AppTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers Swiftlet\App::run
	 */
	public function testRun()
	{
		ob_start();

		App::run();

		$length = ob_get_length();

		ob_end_clean();

		$this->assertGreaterThan(0, $length);
	}

	/**
	 * @covers Swiftlet\App::getAction
	 */
	public function testGetAction()
	{
		$action = App::getAction();

		$this->assertEquals($action, 'indexAction');
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

		$this->assertInstanceOf('Swiftlet\Model',        $model);
		$this->assertInstanceOf('Swiftlet\ExampleModel', $model);

		$this->assertEquals(get_class($model), 'Swiftlet\ExampleModel');
	}

	/**
	 * @covers Swiftlet\App::getSingleton
	 */
	public function testGetSingleton()
	{
		$model  = App::getSingleton('example');
		$model2 = App::getSingleton('example');

		$this->assertInternalType('object', $model);

		$this->assertInstanceOf('Swiftlet\Model',        $model);
		$this->assertInstanceOf('Swiftlet\ExampleModel', $model);

		$this->assertEquals(get_class($model), 'Swiftlet\ExampleModel');

		$model->test = 'test';

		$this->assertSame($model, $model2);
	}

	/**
	 * @covers Swiftlet\App::getView
	 */
	public function testGetView()
	{
		$view = App::getView();

		$this->assertEquals($view, 'index');
	}

	/**
	 * @covers Swiftlet\App::getController
	 */
	public function testGetController()
	{
		$controller = App::getController();

		$this->assertInternalType('object', $controller);

		$this->assertInstanceOf('Swiftlet\Controller',      $controller);
		$this->assertInstanceOf('Swiftlet\IndexController', $controller);

		$this->assertEquals(get_class($controller), 'Swiftlet\IndexController');

		$title = $controller->getTitle();

		$this->assertEquals('Home', $title);
	}

	/**
	 * @covers Swiftlet\App::getPlugins
	 */
	public function testGetPlugins()
	{
		$plugins = App::getPlugins();

		$this->assertInternalType('array', $plugins);

		$this->assertCount(1, $plugins);

		$this->assertInternalType('object', $plugins[0]);
	}

	/**
	 * @covers Swiftlet\App::getHooks
	 */
	public function testGetHooks()
	{
		$hooks = App::getHooks();

		$this->assertInternalType('array', $hooks);

		$this->assertNotEmpty($hooks);

		$this->assertInternalType('string', $hooks[0]);
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

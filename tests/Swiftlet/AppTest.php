<?php

namespace Swiftlet;

require dirname(__FILE__) . '/../../Swiftlet/Interfaces/App.php';
require dirname(__FILE__) . '/../../Swiftlet/App.php';

class AppTest extends \PHPUnit_Framework_TestCase
{
	protected
		$app
		;

	public function setUp()
	{
		$this->app = new App;

		set_error_handler(array($this->app, 'error'), E_ALL | E_STRICT);

		spl_autoload_register(array($this->app, 'autoload'));
	}

	/**
	 * @covers Swiftlet\App::run
	 */
	public function testRun()
	{
		list($view, $controller) = $this->app->run();

		$this->assertInternalType('object', $view);
		$this->assertInternalType('object', $controller);

		$this->assertInstanceOf('Swiftlet\Interfaces\View',       $view);
		$this->assertInstanceOf('Swiftlet\Interfaces\Controller', $controller);
	}

	/**
	 * @covers Swiftlet\App::getControllerName
	 */
	public function testGetControllerName()
	{
		$controllerName = $this->app->getControllerName();

		$this->assertEquals($controllerName, 'Index');
	}

	/**
	 * @covers Swiftlet\App::getAction
	 */
	public function testGetAction()
	{
		$action = $this->app->getAction();

		$this->assertEquals($action, 'index');
	}

	/**
	 * @covers Swiftlet\App::getConfig
	 */
	public function testGetConfig()
	{
		$test = $this->app->getConfig('test');

		$this->assertNull($test, 'test');
	}

	/**
	 * @covers Swiftlet\App::setConfig
	 */
	public function testSetConfig()
	{
		$this->app->setConfig('test', 'test');

		$test = $this->app->getConfig('test');

		$this->assertEquals($test, 'test');
	}

	/**
	 * @covers Swiftlet\App::getArgs
	 */
	public function testGetArgs()
	{
		$args = $this->app->getArgs();

		$this->assertInternalType('array', $args);

		$this->assertEmpty($args);
	}

	/**
	 * @covers Swiftlet\App::getModel
	 */
	public function testGetModel()
	{
		$model = $this->app->getModel('example');

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
		$model  = $this->app->getSingleton('example');
		$model2 = $this->app->getSingleton('example');

		$this->assertInternalType('object', $model);

		$this->assertInstanceOf('Swiftlet\Model',          $model);
		$this->assertInstanceOf('Swiftlet\Models\Example', $model);

		$this->assertEquals(get_class($model), 'Swiftlet\Models\Example');

		$model->test = 'test';

		$this->assertSame($model, $model2);
	}

	/**
	 * @covers Swiftlet\App::getRootPath
	 */
	public function testGetRootPath()
	{
		$rootPath = $this->app->getRootPath();

		$this->assertEquals('/', $rootPath);
	}

	/**
	 * @covers Swiftlet\App::registerHook
	 */
	public function testRegisterHook()
	{
		$this->app->registerHook('test', array());
	}
}

<?php

namespace Swiftlet\Abstracts;

require_once 'vendor/Swiftlet/Interfaces/App.php';
require_once 'vendor/Swiftlet/Interfaces/Common.php';
require_once 'vendor/Swiftlet/Abstracts/Common.php';
require_once 'vendor/Swiftlet/Exception.php';

/**
 * Application class
 * @abstract
 */
abstract class App extends Common implements \Swiftlet\Interfaces\App
{
	/**
	 * Vendor
	 * @var string
	 */
	protected $vendor = 'Swiftlet';

	/**
	 * View instance
	 * @var \Swiftlet\Interfaces\View
	 */
	protected $view;

	/**
	 * Configuration values
	 * @var array
	 */
	protected $config = array();

	/**
	 * Hooks
	 * @var array
	 */
	protected $hooks = array();

	/**
	 * vendor/Plugins
	 * @var array
	 */
	protected $plugins = array();

	/**
	 * Constructor
	 * @param string $vendor
	 * @return App
	 */
	public function __construct(\Swiftlet\Interfaces\View $view, $vendor = null)
	{
		$this->view = $view;

		if ( $vendor ) {
			$this->vendor = $vendor;
		}

		return $this;
	}

	/**
	 * Distpatch the controller
	 * @return App
	 */
	public function dispatchController()
	{
		$controllerNamespace = $this->vendor . '\Controllers';
		$controllerClass     = $controllerNamespace . '\Index';
		$action              = 'index';

		$args = $this->getArgs();

		// Get the controller and action name from the URL
		if ( $args ) {
			$controllerClass = $controllerNamespace . '\\' . str_replace(' ', '\\', ucwords(str_replace('_', ' ', str_replace('-', '', array_shift($args)))));

			if ( $args ) {
				$action = str_replace('-', '', array_shift($args));
			}
		}

		if ( !is_file('vendor/' . str_replace('\\', '/', $controllerClass) . '.php') ) {
			$controllerClass = $controllerNamespace . '\Error404';
		}

		// Instantiate the controller
		$controller = new $controllerClass();

		$this->registerHook('actionBefore', $controller, $this->view);

		$actionExists = false;

		if ( method_exists($controller, $action) ) {
			$method = new \ReflectionMethod($controller, $action);

			if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() ) {
				$actionExists = true;
			}
		}

		if ( !$actionExists ) {
			$controllerClass = $controllerNamespace . '\Error404';
			$action          = 'index';

			$controller = new $controllerClass;
		}

		$controller
			->setApp($this)
			->setView($this->view);

		// Call the controller action
		$controller->{$action}(array_slice($this->getArgs(), 2));

		$this->registerHook('actionAfter', $controller, $this->view);

		return $this;
	}

	/**
	 * Serve the page
	 * @return App
	 */
	public function serve()
	{
		$this->view->vendor   = $this->getVendor();
		$this->view->rootPath = $this->getRootPath();

		$this->view->render();

		return $this;
	}

	/**
	 * Load plugins
	 * @param string $namespace
	 * @return App
	 */
	public function loadPlugins()
	{
		// Load plugins
		if ( $handle = opendir('vendor/' . str_replace('\\', '/', $this->getVendor() . '/Plugins')) ) {
			while ( ( $file = readdir($handle) ) !== false ) {
				$pluginClass = $this->getVendor() . '\Plugins\\' . preg_replace('/\.php$/', '', $file);

				if ( is_file('vendor/' . str_replace('\\', '/', $pluginClass) . '.php') ) {
					$this->plugins[$pluginClass] = array();

					$reflection = new \ReflectionClass($pluginClass);

					$parentClass = $reflection->getParentClass();

					foreach ( get_class_methods($pluginClass) as $methodName ) {
						$method = new \ReflectionMethod($pluginClass, $methodName);

						if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() && !$parentClass->hasMethod($methodName) ) {
							$this->plugins[$pluginClass][] = $methodName;
						}
					}
				}
			}

			ksort($this->plugins);

			closedir($handle);
		}

		return $this;
	}

	/**
	 * Get the vendor
	 * @return string
	 */
	public function getVendor()
	{
		return $this->vendor;
	}

	/**
	 * Get a configuration value
	 * @param string $variable
	 * @return mixed
	 */
	public function getConfig($variable)
	{
		return isset($this->config[$variable]) ? $this->config[$variable] : null;
	}

	/**
	 * Set a configuration value
	 * @param string $variable
	 * @param mixed $value
	 * @return \Swiftlet\Interfaces\App
	 */
	public function setConfig($variable, $value)
	{
		$this->config[$variable] = $value;

		return $this;
	}

	/**
	 * Get a model instance
	 * @param string $modelName
	 * @return \Swiftlet\Interfaces\Model
	 */
	public function getModel($modelName)
	{
		$modelClass = '\\' . $this->getVendor() . '\Models\\' . ucfirst($modelName);

		return new $modelClass;
	}

	/**
	 * Get a library instance
	 * @param string $libraryName
	 * @return \Swiftlet\Interfaces\Library
	 */
	public function getLibrary($libraryName)
	{
		$libraryClass = '\\' . $this->getVendor() . '\Libraries\\' . ucfirst($modelName);

		return new $libraryClass($this);
	}

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath()
	{
		$rootPath = '';

		// Determine the client-side path to root
		if ( !empty($_SERVER['REQUEST_URI']) ) {
			$rootPath = preg_replace('/(index\.php)?(\?.*)?$/', '', rawurldecode($_SERVER['REQUEST_URI']));
		}

		// Run from command line, e.g. "php index.php -q index"
		$opt = getopt('q:');

		if ( isset($opt['q']) ) {
			$_GET['q'] = $opt['q'];
		}

		if ( !empty($_GET['q']) ) {
			$rootPath = preg_replace('/' . preg_quote($_GET['q'], '/') . '$/', '', $rootPath);
		}

		return $rootPath;
	}

	/**
	 * Get the arguments from the URL
	 * @param int $index
	 * @return mixed
	 */
	public function getArgs($index = null)
	{
		$args = array();

		if ( !empty($_GET['q']) ) {
			$args = explode('/', preg_replace('/^public\//', '', rtrim($_GET['q'], '/')));

			if ( is_int($index) ) {
				$args = isset($args[$index]) ? $args[$index] : null;
			}
		}

		return $args;
	}

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param \Swiftlet\Interfaces\Controller $controller
	 * @param \Swiftlet\Interfaces\View $view
	 * @param array $params
	 */
	public function registerHook($hookName, \Swiftlet\Interfaces\Controller $controller, \Swiftlet\Interfaces\View $view, array $params = array())
	{
		$this->hooks[] = $hookName;

		foreach ( $this->plugins as $pluginName => $hooks ) {
			if ( in_array($hookName, $hooks) ) {
				$plugin = new $pluginName();

				$plugin
					->setApp($this)
					->setController($controller)
					->setView($view);

				$plugin->{$hookName}($params);
			}
		}

		return $this;
	}

	/**
	 * Class autoloader
	 * @param string $className
	 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 */
	public function autoload($className)
	{
		preg_match('/(^.+\\\)?([^\\\]+)$/', ltrim($className, '\\'), $match);

		$file = 'vendor/' . str_replace('\\', '/', $match[1]) . str_replace('_', '/', $match[2]) . '.php';

		if ( file_exists($file) ) {
			include $file;
		}
	}

	/**
	 * Convert errors to \ErrorException instances
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 * @throws \ErrorException
	 */
	public function error($number, $string, $file, $line)
	{
		throw new \ErrorException($string, 0, $number, $file, $line);
	}
}

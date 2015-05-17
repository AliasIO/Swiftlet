<?php

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\App as AppInterface;
use \Swiftlet\Interfaces\Controller as ControllerInterface;
use \Swiftlet\Interfaces\View as ViewInterface;
use \Swiftlet\Factories\Controller as ControllerFactory;

/**
 * Application class
 * @abstract
 */
abstract class App extends Common implements AppInterface
{
	/**
	 * Vendor
	 * @var string
	 */
	protected $vendor = 'Swiftlet';

	/**
	 * Vendor path
	 * @var string
	 */
	protected $vendorPath = 'src/';

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
	 * Plugins
	 * @var array
	 */
	protected $plugins = array();

	/**
	 * Constructor
	 * @param \Swiftlet\Interfaces\View $view
	 * @param string $vendor
	 * @param string $vendorPath
	 * @return App
	 */
	public function __construct(ViewInterface $view, $vendor = null, $vendorPath = 'src/')
	{
		$this->view = $view;

		if ( isset($vendor) ) {
			$this->vendor = $vendor;
		}

		if ( isset($vendorPath) ) {
			$this->vendorPath = rtrim($vendorPath, '/') . '/';
		}

		return $this;
	}

	/**
	 * Distpatch the controller
	 * @return App
	 */
	public function dispatchController()
	{
		// Get the controller, action and remaining parameters from the URL
		$requestUri = '';

		$options = getopt('q:');

		if ( isset($options['q']) ) {
			$requestUri = $options['q'];
		}

		if ( isset($_GET['q']) ) {
			$requestUri = preg_replace('/^public\//', '', trim($_GET['q'], '/'));
		}

		$args = $requestUri ? explode('/', $requestUri) : array();

		$controllerName = array_shift($args) ?: 'Index';
		$action         = array_shift($args) ?: 'index';

		// Instantiate the controller
		$controller = ControllerFactory::build($controllerName, $this, $this->view);

		// Get the action and named parameters if custom routes have been specified
		$routes = $controller->getRoutes();

		foreach ( $routes as $route => $method ) {
			$segments = explode('/', $route);

			$regex = '/^' . strtolower($controllerName) . '\\/' . str_replace('/', '\\/', preg_replace('/\(:[^\/]+\)/', '([^/]+)', preg_replace('/([^\/]+)/', '(\\1)', $route))) . '$/';

			preg_match($regex, $requestUri, $matches);

			array_shift($matches);

			if ( $matches ) {
				$action = $method;

				$args = array();

				foreach ( $segments as $i => $segment ) {
					if ( substr($segment, 0, 1) == ':' ) {
						$args[ltrim($segment, ':')] = $matches[$i];
					}
				}

				$break;
			}
		}

		$actionExists = false;

		if ( method_exists($controller, $action) ) {
			$method = new \ReflectionMethod($controller, $action);

			if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() ) {
				$actionExists = true;
			}
		}

		if ( !$actionExists ) {
			$controller = ControllerFactory::build('Error404', $this, $this->view);

			$action = 'index';
		}

		$this->registerHook('actionBefore', $controller, $this->view);

		// Call the controller action
		$controller->{$action}(array_filter($args));

		$this->registerHook('actionAfter', $controller, $this->view);

		return $this;
	}

	/**
	 * Serve the page
	 * @return App
	 */
	public function serve()
	{
		$this->view->vendor     = $this->vendor;
		$this->view->vendorPath = $this->vendorPath;

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
		if ( $handle = opendir($this->vendorPath . str_replace('\\', '/', $this->vendor . '/Plugins')) ) {
			while ( ( $file = readdir($handle) ) !== false ) {
				$pluginClass = $this->vendor . '\Plugins\\' . preg_replace('/\.php$/', '', $file);

				if ( is_file($this->vendorPath . str_replace('\\', '/', $pluginClass) . '.php') ) {
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
	 * Vendor name
	 * @return string
	 */
	public function getVendor()
	{
		return $this->vendor;
	}

	/**
	 * Vendor path
	 * @return string
	 */
	public function getVendorPath()
	{
		return $this->vendorPath;
	}

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param \Swiftlet\Interfaces\Controller $controller
	 * @param \Swiftlet\Interfaces\View $view
	 * @param array $params
	 */
	public function registerHook($hookName, ControllerInterface $controller, ViewInterface $view, array $params = array())
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

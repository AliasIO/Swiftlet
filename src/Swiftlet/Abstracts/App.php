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
	protected $vendor;

	/**
	 * Vendor path
	 * @var string
	 */
	protected $vendorPath;

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
	 * Events
	 * @var array
	 */
	protected $events = array();

	/**
	 * Listener
	 * @var array
	 */
	protected $listeners = array();

	/**
	 * Constructor
	 * @param \Swiftlet\Interfaces\View $view
	 * @param string $vendor
	 * @param string $vendorPath
	 * @return App
	 */
	public function __construct(ViewInterface $view, $vendor = 'Swiftlet', $vendorPath = 'src/')
	{
		$this->view = $view;

		$this->vendor     = $vendor;
		$this->vendorPath = rtrim($vendorPath, '/') . '/';

		$this->view->vendor     = $this->vendor;
		$this->view->vendorPath = $this->vendorPath;
		$this->view->rootPath   = $this->getRootPath();

		return $this;
	}

	/**
	 * Distpatch the controller
	 * @return App
	 */
	public function dispatchController()
	{
		// Get the controller, action and remaining parameters from the URL
		$args = $this->getArgs();

		$controllerName = array_shift($args) ?: 'Index';
		$action         = array_shift($args) ?: 'index';

		// Instantiate the controller
		$controller = ControllerFactory::build($controllerName, $this, $this->view);

		// Get the action and named parameters if custom routes have been specified
		$routes = $controller->getRoutes();

		foreach ( $routes as $route => $method ) {
			$segments = explode('/', $route);

			$requestUri = implode('/', $this->getArgs());

			$regex = '/' . str_replace('/', '\\/', preg_replace('/\(:[^\/]+\)/', '([^/]+)', preg_replace('/([^\/]+)/', '(\\1)', $route))) . '$/';

			preg_match($regex, $requestUri, $matches);

			array_shift($matches);

			if ( $matches ) {
				$action = $method;

				$args = array();

				foreach ( $segments as $i => $segment ) {
					if ( substr($segment, 0, 1) === ':' ) {
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

		$this->trigger('actionBefore', $controller, $this->view);

		// Call the controller action
		$controller->{$action}(array_filter($args));

		$this->trigger('actionAfter', $controller, $this->view);

		return $this;
	}

	/**
	 * Get request URI
	 * @return string
	 */
	public function getArgs()
	{
		$requestUri = '';

		$options = getopt('q:');

		if ( isset($options['q']) ) {
			$requestUri = $options['q'];
		}

		if ( isset($_GET['q']) ) {
			$requestUri = preg_replace('/^public\//', '', trim($_GET['q'], '/'));
		}

		return explode('/', $requestUri) ?: array();
	}

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath()
	{
		$rootPath = '';

		// Determine the client-side path to root
		if ( isset($_SERVER['REQUEST_URI']) ) {
			$rootPath = preg_replace('/(index\.php)?(\?.*)?$/', '', rawurldecode($_SERVER['REQUEST_URI']));
		}

		return preg_replace('/' . preg_quote(implode($this->getArgs(), '/'), '/') . '$/', '', $rootPath);
	}

	/**
	 * Load listeners
	 * @return App
	 */
	public function loadListeners()
	{
		// Load listeners
		if ( $handle = opendir($this->vendorPath . str_replace('\\', '/', $this->vendor . '/Listeners')) ) {
			while ( ( $file = readdir($handle) ) !== false ) {
				$listenerClass = $this->vendor . '\Listeners\\' . preg_replace('/\.php$/', '', $file);

				if ( is_file($this->vendorPath . str_replace('\\', '/', $listenerClass) . '.php') ) {
					$this->listeners[$listenerClass] = array();

					$reflection = new \ReflectionClass($listenerClass);

					$parentClass = $reflection->getParentClass();

					foreach ( get_class_methods($listenerClass) as $methodName ) {
						$method = new \ReflectionMethod($listenerClass, $methodName);

						if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() && !$parentClass->hasMethod($methodName) ) {
							$this->listeners[$listenerClass][] = $methodName;
						}
					}
				}
			}

			ksort($this->listeners);

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
	 * Trigger an event
	 * @param string $event
	 */
	public function trigger($event)
	{
		$this->events[] = $event;

		foreach ( $this->listeners as $listenerName => $events ) {
			if ( in_array($event, $events) ) {
				$listener = new $listenerName();

				$listener->setApp($this);

				$args = func_get_args();

				array_shift($args);

				call_user_func_array(array($listener, $event), $args);
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

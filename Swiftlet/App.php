<?php

namespace Swiftlet;

require 'Swiftlet/Interfaces/App.php';
require 'Swiftlet/AbstractCommon.php';

/**
 * Application class
 */
class App extends AbstractCommon implements Interfaces\App
{
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
	 * Run the application
	 * @param string $controllerNamesapce
	 * @param string $pluginNamesapce
	 * @return array
	 */
	public function run(Interfaces\View $view, $controllerNamespace, $pluginNamespace)
	{
		$controllerClass = $controllerNamespace . '\Index';
		$action          = 'index';

		$args = $this->getArgs();

		// Get the controller and action name from the URL
		if ( $args ) {
			$controllerClass = $controllerNamespace . '\\' . str_replace(' ', '\\', ucwords(str_replace('_', ' ', str_replace('-', '', array_shift($args)))));

			if ( $args ) {
				$action = str_replace('-', '', array_shift($args));
			}
		}

		if ( !is_file('.' . str_replace('\\', '/', $controllerClass) . '.php') ) {
			$controllerClass = $controllerNamespace . '\Error404';
		}

		// Instantiate the controller
		$controller = new $controllerClass();

		// Load plugins
		if ( $handle = opendir('.' . str_replace('\\', '/', $pluginNamespace)) ) {
			while ( ( $file = readdir($handle) ) !== false ) {
				$pluginClass = $pluginNamespace . '\\' . preg_replace('/\.php$/', '', $file);

				if ( is_file('.' . str_replace('\\', '/', $pluginClass) . '.php') ) {
					$this->plugins[$pluginClass] = array();

					foreach ( get_class_methods($pluginClass) as $methodName ) {
						$method = new \ReflectionMethod($pluginClass, $methodName);

						if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() ) {
							$this->plugins[$pluginClass][] = $methodName;
						}
					}
				}
			}

			ksort($this->plugins);

			closedir($handle);
		}

		// Call the controller action
		$this->registerHook('actionBefore', $controller, $view);

		$actionExists = false;

		if ( method_exists($controller, $action) ) {
			$method = new \ReflectionMethod($controller, $action);

			if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() ) {
				$actionExists = true;
			}
		}

		if ( !$actionExists ) {
			$controllerName = $controllerNamespace . '\Error404';
			$action         = 'index';

			$controller = new $controllerName;
		}

		$controller->setView($view);

		$view->setApp($this);

		$controller->{$action}();

		$this->registerHook('actionAfter', $controller, $view);

		return $this;
	}

	/**
	 * Get a configuration value
	 * @param string $variable
	 * @return mixed|null
	 */
	public function getConfig($variable)
	{
		if ( isset($this->config[$variable]) ) {
			return $this->config[$variable];
		}

		return null;
	}

	/**
	 * Set a configuration value
	 * @param string $variable
	 * @param mixed $value
	 * @return Interfaces\App
	 */
	public function setConfig($variable, $value)
	{
		$this->config[$variable] = $value;

		return $this;
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
	 * Get the arguments
	 * @param integer $index
	 * @return mixed
	 */
	public function getArgs($index = null)
	{
		$args = array();

		if ( !empty($_GET['q']) ) {
			$args = explode('/', preg_replace('/^public\//', '', $_GET['q']));

			if ( $index !== null ) {
				return isset($args[$index]) ? $args[$index] : null;
			}
		}

		return $args;
	}

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param Interfaces\Controller $controller
	 * @param Interfaces\View $view
	 * @param array $params
	 */
	public function registerHook($hookName, Interfaces\Controller $controller, Interfaces\View $view, array $params = array())
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

		$file = str_replace('\\', '/', $match[1]) . str_replace('_', '/', $match[2]) . '.php';

		if ( file_exists($file) ) {
			include $file;
		}
	}

	/**
	 * Error handler
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

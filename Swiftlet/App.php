<?php

namespace Swiftlet;

/**
 * Application class
 */
class App implements Interfaces\App
{
	/**
	 * Action name
	 * @var string
	 */
	protected $action = 'index';

	/**
	 * Arguments
	 * @var array
	 */
	protected $args = array();

	/**
	 * Configuration values
	 * @var array
	 */
	protected $config = array();

	/**
	 * Controller intance
	 * @var Interfaces/Controller
	 */
	protected $controller;

	/**
	 * Controller name
	 * @var string
	 */
	protected $controllerName = 'Index';

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
	 * Root path
	 * @var string
	 */
	protected $rootPath = '/';

	/**
	 * Re-usable model instances
	 * @var array
	 */
	protected $singletons = array();

	/**
	 * View instance
	 * @var Interfaces/View
	 */
	protected $view;

	/**
	 * Run the application
	 */
	public function run()
	{
		// Determine the client-side path to root
		if ( !empty($_SERVER['REQUEST_URI']) ) {
			$this->rootPath = preg_replace('/(index\.php)?(\?.*)?$/', '', rawurldecode($_SERVER['REQUEST_URI']));
		}

		// Run from command line, e.g. "php index.php -q index"
		$opt = getopt('q:');

		if ( isset($opt['q']) ) {
			$_GET['q'] = $opt['q'];
		}

		if ( !empty($_GET['q']) ) {
			$this->rootPath = preg_replace('/' . preg_quote($_GET['q'], '/') . '$/', '', $this->rootPath);
		}

		// Extract controller name, view name, action name and arguments from URL
		if ( !empty($_GET['q']) ) {
			$this->args = explode('/', preg_replace('/^public\//', '', $_GET['q']));

			if ( $this->args ) {
				$this->controllerName = str_replace(' ', '/', ucwords(str_replace('_', ' ', str_replace('-', '', array_shift($this->args)))));
			}

			if ( $action = $this->args ? array_shift($this->args) : '' ) {
				$this->action = str_replace('-', '', $action);
			}
		}

		if ( !is_file('Swiftlet/Controllers/' . $this->controllerName . '.php') ) {
			$this->controllerName .= '/Index';

			if ( !is_file('Swiftlet/Controllers/' . $this->controllerName . '.php') ) {
				$this->controllerName = 'Error404';
			}
		}

		$this->view = new View($this, strtolower($this->controllerName));

		// Instantiate the controller
		$controller = 'Swiftlet\Controllers\\' . str_replace('/', '\\', $this->controllerName);

		$this->controller = new $controller($this, $this->view);

		// Load plugins
		if ( $handle = opendir('Swiftlet/Plugins') ) {
			while ( ( $file = readdir($handle) ) !== FALSE ) {
				if ( is_file('Swiftlet/Plugins/' . $file) && preg_match('/^(.+)\.php$/', $file, $match) ) {
					$pluginName = 'Swiftlet\Plugins\\' . $match[1];

					$this->plugins[$pluginName] = array();

					foreach ( get_class_methods($pluginName) as $methodName ) {
						$method = new \ReflectionMethod($pluginName, $methodName);

						if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() ) {
							$this->plugins[$pluginName][] = $methodName;
						}
					}
				}
			}

			ksort($this->plugins);

			closedir($handle);
		}

		// Call the controller action
		$this->registerHook('actionBefore');

		if ( method_exists($this->controller, $this->action) ) {
			$method = new \ReflectionMethod($this->controller, $this->action);

			if ( $method->isPublic() && !$method->isFinal() && !$method->isConstructor() ) {
				$this->controller->{$this->action}();
			} else {
				$this->controller = new Controllers\Error404($this, $this->view);

				$this->view->name = 'error404';

				$this->controller->index();
			}
		} else {
			$this->controller = new Controllers\Error404($this, $this->view);

			$this->view->name = 'error404';

			$this->controller->index();
		}

		$this->registerHook('actionAfter');

		return array($this->view, $this->controller);
	}

	/**
	 * Serve the page
	 */
	public function serve()
	{
		ob_start();

		$this->view->render();

		ob_end_flush();
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
	 */
	public function setConfig($variable, $value)
	{
		$this->config[$variable] = $value;
	}

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath()
	{
		return $this->rootPath;
	}

	/**
	 * Get the controller name
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * Get the action name
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Get the arguments
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}

	/**
	 * Get a model
	 * @param string $modelName
	 * @return object
	 */
	public function getModel($modelName)
	{
		$modelName = 'Swiftlet\Models\\' . ucfirst($modelName);

		// Instantiate the model
		return new $modelName($this);
	}

	/**
	 * Get a model singleton
	 * @param string $modelName
	 * @return object
	 */
	public function getSingleton($modelName)
	{
		if ( isset($this->singletons[$modelName]) ) {
			return $this->singletons[$modelName];
		}

		$model = $this->getModel($modelName);

		$this->singletons[$modelName] = $model;

		return $model;
	}

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param array $params
	 */
	public function registerHook($hookName, array $params = array())
	{
		$this->hooks[] = $hookName;

		foreach ( $this->plugins as $pluginName => $hooks ) {
			if ( in_array($hookName, $hooks) ) {
				$plugin = new $pluginName($this, $this->view, $this->controller);

				$plugin->{$hookName}($params);
			}
		}
	}

	/**
	 * Class autoloader
	 * @param $className
	 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 */
	public function autoload($className)
	{
		preg_match('/(^.+\\\)?([^\\\]+)$/', ltrim($className, '\\'), $match);

		$file = str_replace('\\', '/', $match[1]) . str_replace('_', '/', $match[2]) . '.php';

		if ( file_exists($file) ) {
			require $file;
		}
	}

	/**
	 * Error handler
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 * @throws Exception
	 */
	public function error($number, $string, $file, $line)
	{
		throw new Exception('Error #' . $number . ': ' . $string . ' in ' . $file . ' on line ' . $line);
	}
}

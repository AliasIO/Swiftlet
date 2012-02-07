<?php

namespace Swiftlet;

final class App
{
	const
		VERSION = '3.0'
		;

	private static
		$_action     = 'index',
		$_args       = array(),
		$_controller,
		$_hooks      = array(),
		$_plugins    = array(),
		$_rootPath   = '/',
		$_singletons = array(),
		$_view
		;

	/**
	 * Initialize the application
	 */
	public static function run()
	{
		set_error_handler(array('Swiftlet\App', 'error'), E_ALL);

		// Determine the client-side path to root
		if ( !empty($_SERVER['REQUEST_URI']) ) {
			self::$_rootPath = preg_replace('/(index\.php)?(\?.*)?$/', '', $_SERVER['REQUEST_URI']);

			if ( !empty($_GET['q']) ) {
				self::$_rootPath = preg_replace('/' . preg_quote($_GET['q'], '/') . '$/', '', self::$_rootPath);
			}
		}

		// Extract controller name, view name, action name and arguments from URL
		$controllerName = 'Index';

		if ( !empty($_GET['q']) ) {
			self::$_args = explode('/', $_GET['q']);

			if ( self::$_args ) {
				$controllerName = str_replace(' ', '/', ucwords(str_replace('_', ' ', array_shift(self::$_args))));
			}

			if ( $action = self::$_args ? array_shift(self::$_args) : '' ) {
				self::$_action = $action;
			}
		}

		if ( !is_file('controllers/' . $controllerName . '.php') ) {
			$controllerName = 'Error404';
		}

		self::$_view = strtolower($controllerName);

		// Instantiate the controller
		require 'controllers/' . $controllerName . '.php';

		$controllerName = 'Swiftlet\Controllers\\' . basename($controllerName);

		self::$_controller = new $controllerName();

		// Load plugins
		if ( $handle = opendir('plugins') ) {
			while ( ( $file = readdir($handle) ) !== FALSE ) {
				if ( is_file('plugins/' . $file) && preg_match('/^(.+)\.php$/', $file, $match) ) {
					$pluginName = 'Swiftlet\Plugins\\' . $match[1];

					require 'plugins/' . $file;

					self::$_plugins[] = new $pluginName();
				}
			}

			sort(self::$_plugins);

			closedir($handle);
		}

		// Call the controller action
		$method = new \ReflectionMethod(self::$_controller, self::$_action);

		if ( !$method->isPublic() || $method->isFinal() ) {
			self::$_action = 'notImplemented';
		}

		self::registerHook('actionBefore');

		self::$_controller->{self::$_action}();

		self::registerHook('actionAfter');

		return true;
	}

	/**
	 * Serve the page
	 */
	public static function serve()
	{
		// Render the view
		if ( is_file($file = 'views/' . self::$_view . '.html.php') ) {
			header('X-Generator: Swiftlet ' . self::VERSION);

			require $file;
		} else {
			throw new \Exception('View not found');
		}
	}

	/**
	 * Get the action name
	 * @return string
	 */
	public static function getAction()
   	{
		return self::$_action;
	}

	/**
	 * Get the arguments
	 * @return array
	 */
	public static function getArgs()
   	{
		return self::$_args;
	}

	/**
	 * Get a model
	 * @param string $modelName
	 * @return object
	 */
	public static function getModel($modelName)
   	{
		$modelName = ucfirst($modelName);

		if ( is_file($file = 'models/' . $modelName . '.php') ) {
			$modelName = 'Swiftlet\Models\\' . $modelName;

			if ( !class_exists($modelName) ) require $file;

			// Instantiate the model
			return new $modelName();
		} else {
			throw new \Exception($modelName . ' not found');
		}
	}

	/**
	 * Get a model singleton
	 * @param string $modelName
	 * @return object
	 */
	public static function getSingleton($modelName)
	{
		if ( isset(self::$_singletons[$modelName]) ) {
			return self::$_singletons[$modelName];
		}

		$model = self::getModel($modelName);

		self::$_singletons[$modelName] = $model;

		return $model;
	}

	/**
	 * Get the view name
	 * @return string
	 */
	public static function getView()
   	{
		return self::$_view;
	}

	/**
	 * Change the view
	 * @param string $view
	 */
	public static function setView($view)
   	{
		self::$_view = $view;
	}

	/**
	 * Get the controller instance
	 * @return object
	 */
	public static function getController()
   	{
		return self::$_controller;
	}

	/**
	 * Get all plugin instances
	 * @return array
	 */
	public static function getPlugins()
   	{
		return self::$_plugins;
	}

	/**
	 * Get all registered hooks
	 * @return array
	 */
	public static function getHooks()
   	{
		return self::$_hooks;
	}

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public static function getRootPath()
	{
		return self::$_rootPath;
	}

	/**
	 * Register a new hook for plugins to implement
	 * @param string $hookName
	 * @param array $params
	 */
	public static function registerHook($hookName, $params = array()) {
		self::$_hooks[] = $hookName;

		$hookName .= 'Hook';

		foreach ( self::$_plugins as $plugin ) {
			if ( method_exists($plugin, $hookName) ) {
				$plugin->{$hookName}($params);
			}
		}
	}

	/**
	 * Error handler
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 */
	public static function error($number, $string, $file, $line)
	{
		throw new \Exception('Error #' . $number . ': ' . $string . ' in ' . $file . ' on line ' . $line);
	}
}

<?php

final class Swiftlet
{
	const
		VERSION = '3.0'
		;

	protected static
		$_action     = 'indexAction',
		$_args       = array(),
		$_controller,
		$_plugins    = array(),
		$_rootPath   = '/',
		$_singletons = array(),
		$_view       = 'index'
		;

	/**
	 * Initialize the application
	 */
	public static function run()
	{
		// Determine the client-side path to root
		$path = dirname(dirname(__FILE__));

		if ( !empty($_SERVER['DOCUMENT_ROOT']) && preg_match('/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/') . '/', $path) ) {
			$path = preg_replace('/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/') . '/', '', $path);

			self::$_rootPath = rtrim($path, '/') . '/';
		}

		// Extract controller name, view name, action name and arguments from URL
		$controllerName = 'IndexController';

		if ( !empty($_GET['q']) ) {
			self::$_args = explode('/', $_GET['q']);

			if ( self::$_args ) {
				self::$_view = array_shift(self::$_args);

				$controllerName = ucfirst(self::$_view) . 'Controller';
			}

			if ( $action = self::$_args ? array_shift(self::$_args) : '' ) {
				self::$_action = $action . 'Action';
			}
		}

		if ( !is_file('controllers/' . $controllerName . '.php') ) {
			$controllerName = 'Error404Controller';
			self::$_view    = 'error404';
		}

		// Instantiate the controller
		require('controllers/' . $controllerName . '.php');

		self::$_controller = new $controllerName();

		// Load plugins
		if ( $handle = opendir('plugins') ) {
			while ( ( $file = readdir($handle) ) !== FALSE ) {
				if ( is_file('plugins/' . $file) && preg_match('/^(.+Plugin)\.php$/', $file, $match) ) {
					$pluginName = $match[1];

					require('plugins/' . $file);

					self::$_plugins[] = new $pluginName();
				}
			}

			ksort(self::$_plugins);

			closedir($handle);
		}

		// Call the controller action
		if ( !method_exists(self::$_controller, self::$_action) ) {
			self::$_action = 'notImplementedAction';
		}

		self::registerHook('actionBefore');

		self::$_controller->{self::$_action}();

		self::registerHook('actionAfter');

		// Render the view
		if ( is_file($file = 'views/' . self::$_view . '.html.php') ) {
			header('X-Generator: Swiftlet ' . self::VERSION);

			require($file);
		} else {
			throw new Exception('View not found');
		}
	}

	/**
	 * Get the action name
	 * @return string
	 */
	public static function getAction()
   	{
		return self::$_action();
	}

	/**
	 * Get the arguments
	 * @return array
	 */
	public static function getArgs()
   	{
		return self::$_args();
	}

	/**
	 * Get a model
	 * @param string $modelName
	 * @return object
	 */
	public static function getModel($modelName)
   	{
		$modelName = ucfirst($modelName) . 'Model';

		if ( is_file($file = 'models/' . $modelName . '.php') ) {
			// Instantiate the model
			if ( !class_exists($modelName) ) require($file);

			return new $modelName();
		} else {
			throw new Exception($modelName . ' not found');
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

		$model = Swiftlet::getModel($modelName);

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
	 * Get the controller instance
	 * @return object
	 */
	public static function getController()
   	{
		return self::$_controller;
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
		throw new Exception('Error #' . $number . ': ' . $string . ' in ' . $file . ' on line ' . $line);
	}
}

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
		set_error_handler(array('Swiftlet\App', 'error'), E_ALL | E_STRICT);

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

		if ( !is_file('Swiftlet/Controllers/' . $controllerName . '.php') ) {
			$controllerName = 'Error404';
		}

		self::$_view = strtolower($controllerName);

		// Instantiate the controller
		$controllerName = 'Swiftlet\Controllers\\' . basename($controllerName);

		self::$_controller = new $controllerName();

		// Load plugins
		if ( $handle = opendir('Swiftlet/Plugins') ) {
			while ( ( $file = readdir($handle) ) !== FALSE ) {
				if ( is_file('Swiftlet/Plugins/' . $file) && preg_match('/^(.+)\.php$/', $file, $match) ) {
					$pluginName = 'Swiftlet\Plugins\\' . $match[1];

					self::$_plugins[$pluginName] = array(
						'hooks' => array()
						);

					foreach ( get_class_methods($pluginName) as $methodName ) {
						$method = new \ReflectionMethod($pluginName, $methodName);

						if ( $method->isPublic() && !$method->isFinal() ) {
							self::$_plugins[$pluginName]['hooks'][] = $methodName;
						}
					}
				}
			}

			ksort(self::$_plugins);

			closedir($handle);
		}

		// Call the controller action
		self::registerHook('actionBefore');

		if ( method_exists(self::$_controller, self::$_action) ) {
			$method = new \ReflectionMethod(self::$_controller, self::$_action);

			if ( $method->isPublic() && !$method->isFinal() ) {
				self::$_controller->{self::$_action}();
			} else {
				self::$_controller->notImplemented();
			}
		} else {
			self::$_controller->notImplemented();
		}

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
		$modelName = 'Swiftlet\Models\\' . ucfirst($modelName);

		// Instantiate the model
		return new $modelName();
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
	public static function registerHook($hookName, array $params = array()) {
		self::$_hooks[] = $hookName;

		foreach ( self::$_plugins as $pluginName => $plugin ) {
			if ( in_array($hookName, $plugin['hooks']) ) {
				if ( !isset($plugin['instance']) ) {
					// Instantiate the plugin
					self::$_plugins[$pluginName]['instance'] = $plugin['instance'] = new $pluginName;
				}

				$plugin['instance']->{$hookName}($params);
			}
		}
	}

	/**
	 * Class autoloader
	 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 */
	public static function autoload($className)
	{
		preg_match('/(^.+\\\)?([^\\\]+)$/', ltrim($className, '\\'), $match);

		$file = str_replace('\\', '/', $match[1]) . str_replace('_', '/', $match[2]) . '.php';

		require $file;
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

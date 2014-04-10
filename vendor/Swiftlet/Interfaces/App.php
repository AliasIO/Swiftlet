<?php

namespace Swiftlet\Interfaces;

require_once 'vendor/Swiftlet/Interfaces/Common.php';

/**
 * Application interface
 */
interface App extends Common
{
	/**
	 * Constructor
	 * @param View $view
	 * @param string $vendor
	 * @return App
	 */
	public function __construct(View $view, $vendor);

	/**
	 * Dispatch the controller
	 * @return App
	 */
	public function dispatchController();

	/**
	 * Load plugins
	 * @return App
	 */
	public function loadPlugins();

	/**
	 * Get a configuration value
	 * @param string $variable
	 * @return mixed|null
	 */
	public function getConfig($variable);

	/**
	 * Set a configuration value
	 * @param string $variable
	 * @param mixed $value
	 * @return App
	 */
	public function setConfig($variable, $value);

	/**
	 * Get a model instance
	 * @param string $modelName
	 * @return \Swiftlet\Interfaces\Model
	 */
	public function getModel($modelName);

	/**
	 * Get a library instance
	 * @param string $libraryName
	 * @return \Swiftlet\Interfaces\Library
	 */
	public function getLibrary($libraryName);

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param Interfaces\Controller $controller
	 * @param Interfaces\View $view
	 * @param array $params
	 * @return App
	 */
	public function registerHook($hookName, Controller $controller, View $view, array $params);

	/**
	 * Class autoloader
	 * @param $className
	 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 */
	public function autoload($className);

	/**
	 * Convert errors to \ErrorException instances
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 * @throws Exception
	 */
	public function error($number, $string, $file, $line);
}

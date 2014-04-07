<?php

namespace Swiftlet\Interfaces;

require_once 'vendor/Swiftlet/Interfaces/Common.php';

/**
 * Application interface
 */
interface App extends Common
{
	/**
	 * Run the application
	 * @param string $controllerNamesapce
	 * @param \Swiftlet\Interfaces\View $view
	 * @return array
	 */
	public function run($controllerNamespace = '\Swiftlet\Controllers', \Swiftlet\Interfaces\View $view);

	/**
	 * Load plugins
	 * @param string $namespace
	 * @return App
	 */
	public function loadPlugins($namespace);

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
	 * Get the arguments
	 * @param integer $index
	 * @return mixed
	 */
	public function getArgs($index = null);

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath();

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param Interfaces\Controller $controller
	 * @param Interfaces\View $view
	 * @param array $params
	 * @return App
	 */
	public function registerHook($hookName, Controller $controller, View $view, array $params = array());

	/**
	 * Class autoloader
	 * @param $className
	 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 */
	public function autoload($className);

	/**
	 * Error handler
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 * @throws Exception
	 */
	public function error($number, $string, $file, $line);
}

<?php

namespace Swiftlet\Interfaces;

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
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param Interfaces\Controller $controller
	 * @param Interfaces\View $view
	 * @param array $params
	 * @return App
	 */
	public function registerHook($hookName, Controller $controller, View $view, array $params);

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

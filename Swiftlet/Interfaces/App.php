<?php

namespace Swiftlet\Interfaces;

require 'Swiftlet/Interfaces/Common.php';

/**
 * Application interface
 */
interface App extends Common
{
	/**
	 * Constructor
	 * @param string $namespace
	 */
	public function __construct($namespace = 'Swiftlet');

	/**
	 * Run the application
	 */
	public function run();

	/**
	 * Serve the page
	 */
	public function serve();

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
	 */
	public function setConfig($variable, $value);

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath();

	/**
	 * Get the controller name
	 * @return string
	 */
	public function getControllerName();

	/**
	 * Get the action name
	 * @return string
	 */
	public function getAction();

	/**
	 * Get the arguments
	 * @param integer $index
	 * @return mixed
	 */
	public function getArgs($index);

	/**
	 * Get a model
	 * @param string $modelName
	 * @return object
	 */
	public function getModel($modelName);

	/**
	 * Get a model singleton
	 * @param string $modelName
	 * @return object
	 */
	public function getSingleton($modelName);

	/**
	 * Register a hook for plugins to implement
	 * @param string $hookName
	 * @param array $params
	 */
	public function registerHook($hookName, array $params = array());

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

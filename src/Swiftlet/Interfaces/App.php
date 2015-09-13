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
	 * Get request URI
	 * @return string
	 */
	public function getArgs();

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath();

	/**
	 * Load listeners
	 * @return App
	 */
	public function loadListeners();

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
	 * Trigger an event
	 * @param string $event
	 * @return App
	 */
	public function trigger($event);

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

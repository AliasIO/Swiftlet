<?php

declare(strict_types=1);

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
	public function __construct(View $view, string $vendor, string $vendorPath);

	/**
	 * Dispatch the controller
	 * @return App
	 */
	public function dispatchController(): App;

	/**
	 * Get request URI
	 * @return string
	 */
	public function getArgs(): array;

	/**
	 * Get the client-side path to root
	 * @return string
	 */
	public function getRootPath(): string;

	/**
	 * Load listeners
	 * @return App
	 */
	public function loadListeners(): App;

	/**
	 * Get a configuration value
	 * @param string $variable
	 * @return mixed|null
	 */
	public function getConfig(string $variable);

	/**
	 * Set a configuration value
	 * @param string $variable
	 * @param mixed $value
	 * @return App
	 */
	public function setConfig(string $variable, $value): App;

	/**
	 * Trigger an event
	 * @param string $event
	 * @return App
	 */
	public function trigger(string $event): App;

	/**
	 * Convert errors to \ErrorException instances
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 * @throws Exception
	 */
	public static function error(int $number, string $string, string $file, int $line);
}

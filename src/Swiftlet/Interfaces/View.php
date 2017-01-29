<?php

declare(strict_types=1);

namespace Swiftlet\Interfaces;

/**
 * View interface
 */
interface View extends Common
{
	/**
	 * Get a view variable
	 * @param string $variable
	 * @param bool $htmlEncode
	 * @return mixed|null
	 */
	public function get(string $variable, bool $htmlEncode);

	/**
	 * Magic method to get a view variable, forwards to $this->get()
	 * @param string $variable
	 * @return mixed
	 */
	public function __get(string $variable);

	/**
	 * Magic method to set a view variable, forwards to $this->set()
	 * @param string $variable
	 * @param mixed $value
	 */
	public function __set(string $variable, $value): View;

	/**
	 * Magic method to set a view variable, forwards to $this->set()
	 * @param string $variable
	 * @param mixed $value
	 * @return Interfaces\View
	 */
	public function set(string $variable, $value): View;

	/**
	 * Recursively make a value safe for HTML
	 * @param mixed $value
	 * @return mixed
	 */
	public function htmlEncode($value);

	/**
	 * Recursively decode an HTML encoded value
	 * @param mixed $value
	 * @return mixed
	 */
	public function htmlDecode($value);

	/**
	 * Render the view
	 * @return Interfaces\View
	 * @throws Exception
	 */
	public function render(): View;
}

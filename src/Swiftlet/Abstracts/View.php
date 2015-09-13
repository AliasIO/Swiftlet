<?php

namespace Swiftlet\Abstracts;

use \Swiftlet\Interfaces\View as ViewInterface;

/**
 * View class
 * @abstract
 */
abstract class View extends Common implements ViewInterface
{
	/**
	 * Vendor name
	 * @var string
	 */
	public $vendor = 'Swiftlet';

	/**
	 * Vendor path
	 * @var string
	 */
	public $vendorPath = 'src/';

	/**
	 * Client side path to root
	 * @var string
	 */
	public $rootPath = '';

	/**
	 * View variables
	 * @var array
	 */
	protected $variables = array();

	/**
	 * View name
	 * @var string
	 */
	public $name;

	/**
	 * Get a view variable
	 * @param string $variable
	 * @param bool $htmlEncode
	 * @return mixed|null
	 */
	public function get($variable, $htmlEncode = true)
	{
		$value = null;

		if ( isset($this->variables[$variable]) ) {
			$value = $this->variables[$variable][$htmlEncode ? 'safe' : 'unsafe'];
		}

		return $value;
	}

	/**
	 * Magic method to get a view variable, forwards to $this->get()
	 * @param string $variable
	 * @return mixed
	 */
	public function __get($variable)
	{
		return $this->get($variable);
	}

	/**
	 * Set a view variable
	 * @param string $variable
	 * @param mixed $value
	 * @return \Swiftlet\Interfaces\View
	 */
	public function set($variable, $value = null)
	{
		$this->variables[$variable] = array(
			'safe'   => $this->htmlEncode($value),
			'unsafe' => $value
			);

		return $this;
	}

	/**
	 * Magic method to set a view variable, forwards to $this->set()
	 * @param string $variable
	 * @param mixed $value
	 */
	public function __set($variable, $value = null)
	{
		$this->set($variable, $value);
	}

	/**
	 * Recursively make a value safe for HTML
	 * @param mixed $value
	 * @return mixed
	 */
	public function htmlEncode($value)
	{
		switch ( gettype($value) ) {
			case 'array':
				foreach ( $value as $k => $v ) {
					$value[$k] = $this->htmlEncode($v);
				}

				break;
			case 'object':
				$value = clone $value;

				foreach ( $value as $k => $v ) {
					$value->$k = $this->htmlEncode($v);
				}

				break;
			case 'string':
				$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		}

		return $value;
	}

	/**
	 * Recursively decode an HTML encoded value
	 * @param mixed $value
	 * @return mixed
	 */
	public function htmlDecode($value)
	{
		switch ( gettype($value) ) {
			case 'array':
				foreach ( $value as $k => $v ) {
					$value[$k] = $this->htmlDecode($v);
				}

				break;
			case 'object':
				$value = clone $value;

				foreach ( $value as $k => $v ) {
					$value->$k = $this->htmlDecode($v);
				}

				break;
			default:
				$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		}

		return $value;
	}

	/**
	 * Render the view
	 * @return \Swiftlet\Interfaces\View
	 * @throws \Swiftlet\Exception
	 */
	public function render()
	{
		if ( is_file($file = $this->vendorPath . $this->vendor . '/views/' . $this->name . '.php') ) {
			if ( !headers_sent() ) {
				header('X-Generator: Swiftlet');
			}

			include $file;
		} else {
			throw new \Swiftlet\Exception('View not found');
		}

		return $this;
	}
}

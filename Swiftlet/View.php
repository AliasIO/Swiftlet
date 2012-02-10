<?php

namespace Swiftlet;

final class View implements Interfaces\View
{
	private static
		$_app,
		$_name,
		$_variables = array()
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
	 */
	public function __construct(App $app, $name)
   	{
		self::$_app  = $app;
		self::$_name = $name;
	}

	/**
	 * Get the view name
	 * @return string
	 */
	public static function getName()
	{
		return self::$_name;
	}

	/**
	 * Set the view name
	 * @paran string $view
	 */
	public static function setName($name)
	{
		self::$_name = $name;
	}

	/**
	 * Get a view variable
	 * @params string $variable
	 * @params bool $htmlEncode
	 * @return mixed
	 */
	public static function get($variable, $htmlEncode = true)
   	{
		if ( isset(self::$_variables[$variable]) ) {
			if ( $htmlEncode ) {
				return self::htmlEncode(self::$_variables[$variable]);
			} else {
				return self::$_variables[$variable];
			}
		}
	}

	/**
	 * Set a view variable
	 * @param string $variable
	 * @param mixed $value
	 */
	public static function set($variable, $value = null)
	{
		self::$_variables[$variable] = $value;
	}

	/**
	 * Recursively make a value safe for HTML
	 * @param mixed $value
	 * @return mixed
	 */
	public static function htmlEncode($value)
   	{
		switch ( gettype($value) ) {
			case 'array':
				foreach ( $value as $k => $v ) {
					$value[$k] = self::htmlEncode($v);
				}

				break;
			case 'object':
				foreach ( $value as $k => $v ) {
					$value->$k = self::htmlEncode($v);
				}

				break;
			default:
				$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		}

		return $value;
	}

	/**
	 * Recursively decode an HTML encoded value
	 * @param mixed $value
	 * @return mixed
	 */
	public static function htmlDecode($value)
   	{
		switch ( gettype($value) ) {
			case 'array':
				foreach ( $value as $k => $v ) {
					$value[$k] = self::htmlDecode($v);
				}

				break;
			case 'object':
				foreach ( $value as $k => $v ) {
					$value->$k = self::htmlDecode($v);
				}

				break;
			default:
				$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		}

		return $value;
	}

	/**
	 * Render the view
	 * @param mixed $value
	 * @return mixed
	 */
	public static function render()
   	{
		if ( is_file($file = 'views/' . self::$_name . '.html.php') ) {
			header('X-Generator: Swiftlet');

			require $file;
		} else {
			throw new \Exception('View not found');
		}
	}
}

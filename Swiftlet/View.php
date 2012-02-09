<?php

namespace Swiftlet;

final class View
{
	private static
		$_variables = array()
		;

	/**
	 * Get the page title
	 * @return string
	 */
	public static function getTitle()
	{
		return self::htmlEncode(App::getController()->getTitle());
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
}

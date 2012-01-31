<?php

final class SwiftletView
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
		return self::htmlEncode(Swiftlet::getController()->getTitle());
	}

	/**
	 * Get a view variable
	 * @params string $variable
	 * @return string
	 */
	public static function get($variable)
   	{
		if ( isset(self::$_variables[$variable]) ) {
			return self::htmlEncode(self::$_variables[$variable]);
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
	 * Make a string safe for HTML
	 * @param string $string
	 * @return string
	 */
	public static function htmlEncode($string)
   	{
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Decode an HTML encoded string
	 * @param string $string
	 * @return string
	 */
	public static function htmlDecode($string)
   	{
		return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
	}
}

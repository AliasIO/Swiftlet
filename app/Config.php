<?php

namespace Swiftlet;

final class Config
{
	private static
		$_variables = array()
		;

	/**
	 * Get a config variable
	 * @params string $variable
	 * @return string
	 */
	public static function get($variable)
   	{
		if ( isset(self::$_variables[$variable]) ) {
			return self::$_variables[$variable];
		}
	}

	/**
	 * Set a config variable
	 * @param string $variable
	 * @param mixed $value
	 */
	public static function set($variable, $value = null)
	{
		self::$_variables[$variable] = $value;
	}
}

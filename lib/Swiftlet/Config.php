<?php

namespace Swiftlet;

final class Config
{
	private static
		$_variables = array()
		;

	/**
	 * Get a configuration variable
	 * @params string $variable
	 * @return mixed
	 */
	public static function get($variable)
   	{
		return isset(self::$_variables[$variable]) ? self::$_variables[$variable] : null;
	}

	/**
	 * Set a configuration variable
	 * @param string $variable
	 * @param mixed $value
	 */
	public static function set($variable, $value = null)
	{
		self::$_variables[$variable] = $value;
	}
}

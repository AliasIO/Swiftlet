<?php

namespace Swiftlet\Interfaces;

interface App
{
	public static function run();

	public static function serve();

	public static function getAction();

	public static function getArgs();

	public static function getModel($modelName);

	public static function getSingleton($modelName);

	public static function getRootPath();

	public static function registerHook($hookName, array $params = array());

	public static function autoload($className);

	public static function error($number, $string, $file, $line);
}
